-- ============================================================================
-- SIGPCB - SCRIPTS DE VALIDATION POUR POWER BI
-- ============================================================================
-- Ces scripts permettent de valider que les données sont correctes
-- et que les requêtes Power BI fonctionnent correctement
-- ============================================================================
-- Date: 2025-12-01
-- Usage: Exécuter sur PostgreSQL avant d'importer dans Power BI
-- ============================================================================

-- ============================================================================
-- SECTION 1: VALIDATION DES TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1.1 Vérifier l'existence de toutes les tables requises
-- ----------------------------------------------------------------------------
SELECT
    tablename AS nom_table,
    schemaname AS schema,
    CASE
        WHEN tablename IN (
            'dossier_sessions',
            'langues',
            'categorie_permis',
            'auto_ecoles',
            'annexe_anatts',
            'examens',
            'candidats'
        ) THEN '✓ Requis'
        ELSE '○ Optionnel'
    END AS statut
FROM pg_tables
WHERE schemaname = 'public'
  AND tablename IN (
      'dossier_sessions',
      'langues',
      'categorie_permis',
      'auto_ecoles',
      'annexe_anatts',
      'examens',
      'candidats',
      'authenticites',
      'duplicatas',
      'prorogations',
      'echanges',
      'permis_internationals',
      'licences',
      'demande_agrements',
      'demande_licences',
      'inspecteurs',
      'examinateurs',
      'users'
  )
ORDER BY statut DESC, nom_table;

-- ----------------------------------------------------------------------------
-- 1.2 Vérifier le nombre d'enregistrements dans chaque table
-- ----------------------------------------------------------------------------
SELECT
    'dossier_sessions' AS table_name,
    COUNT(*) AS nombre_lignes,
    MIN(created_at) AS date_min,
    MAX(created_at) AS date_max
FROM dossier_sessions
UNION ALL
SELECT 'langues', COUNT(*), MIN(created_at), MAX(created_at) FROM langues
UNION ALL
SELECT 'categorie_permis', COUNT(*), MIN(created_at), MAX(created_at) FROM categorie_permis
UNION ALL
SELECT 'auto_ecoles', COUNT(*), MIN(created_at), MAX(created_at) FROM auto_ecoles
UNION ALL
SELECT 'annexe_anatts', COUNT(*), MIN(created_at), MAX(created_at) FROM annexe_anatts
UNION ALL
SELECT 'examens', COUNT(*), MIN(created_at), MAX(created_at) FROM examens;

-- ============================================================================
-- SECTION 2: VALIDATION DE LA QUALITÉ DES DONNÉES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 2.1 Vérifier les valeurs NULL dans les colonnes importantes
-- ----------------------------------------------------------------------------
SELECT
    'dossier_sessions' AS table_name,
    COUNT(*) AS total_lignes,
    SUM(CASE WHEN npi IS NULL THEN 1 ELSE 0 END) AS npi_null,
    SUM(CASE WHEN examen_id IS NULL THEN 1 ELSE 0 END) AS examen_id_null,
    SUM(CASE WHEN categorie_permis_id IS NULL THEN 1 ELSE 0 END) AS categorie_permis_id_null,
    SUM(CASE WHEN langue_id IS NULL THEN 1 ELSE 0 END) AS langue_id_null,
    SUM(CASE WHEN annexe_id IS NULL THEN 1 ELSE 0 END) AS annexe_id_null,
    SUM(CASE WHEN date_inscription IS NULL THEN 1 ELSE 0 END) AS date_inscription_null
FROM dossier_sessions;

-- ----------------------------------------------------------------------------
-- 2.2 Vérifier les doublons de NPI par examen
-- ----------------------------------------------------------------------------
SELECT
    npi,
    examen_id,
    COUNT(*) AS nombre_occurrences
FROM dossier_sessions
WHERE state = 'validate'
  AND abandoned = false
GROUP BY npi, examen_id
HAVING COUNT(*) > 1
ORDER BY nombre_occurrences DESC
LIMIT 10;

-- Si résultat vide = bon, sinon investiguer les doublons

-- ----------------------------------------------------------------------------
-- 2.3 Vérifier les valeurs incohérentes
-- ----------------------------------------------------------------------------
-- Cas incohérents: Admis Conduite sans être Admis Code
SELECT
    COUNT(*) AS candidats_incoherents,
    ROUND(100.0 * COUNT(*) / (SELECT COUNT(*) FROM dossier_sessions WHERE state = 'validate'), 2) AS pourcentage
FROM dossier_sessions
WHERE state = 'validate'
  AND abandoned = false
  AND resultat_conduite = 'success'
  AND resultat_code <> 'success';

-- Devrait être 0 ou très faible

-- ----------------------------------------------------------------------------
-- 2.4 Vérifier les dates incohérentes
-- ----------------------------------------------------------------------------
SELECT
    COUNT(*) AS dates_incoherentes
FROM dossier_sessions
WHERE date_validation < date_inscription
   OR date_payment < date_inscription;

-- Devrait être 0

-- ============================================================================
-- SECTION 3: VALIDATION DES RELATIONS (FOREIGN KEYS)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 3.1 Vérifier les clés étrangères cassées - Langues
-- ----------------------------------------------------------------------------
SELECT
    COUNT(*) AS cles_etrangeres_cassees_langues
FROM dossier_sessions ds
LEFT JOIN langues l ON ds.langue_id = l.id
WHERE ds.langue_id IS NOT NULL
  AND l.id IS NULL;

-- Devrait être 0

-- ----------------------------------------------------------------------------
-- 3.2 Vérifier les clés étrangères cassées - Catégories de Permis
-- ----------------------------------------------------------------------------
SELECT
    COUNT(*) AS cles_etrangeres_cassees_categories
FROM dossier_sessions ds
LEFT JOIN categorie_permis cp ON ds.categorie_permis_id = cp.id
WHERE ds.categorie_permis_id IS NOT NULL
  AND cp.id IS NULL;

-- Devrait être 0

-- ----------------------------------------------------------------------------
-- 3.3 Vérifier les clés étrangères cassées - Auto-Écoles
-- ----------------------------------------------------------------------------
SELECT
    COUNT(*) AS cles_etrangeres_cassees_autoecoles
FROM dossier_sessions ds
LEFT JOIN auto_ecoles ae ON ds.auto_ecole_id = ae.id
WHERE ds.auto_ecole_id IS NOT NULL
  AND ae.id IS NULL;

-- Devrait être 0

-- ----------------------------------------------------------------------------
-- 3.4 Vérifier les clés étrangères cassées - Examens
-- ----------------------------------------------------------------------------
SELECT
    COUNT(*) AS cles_etrangeres_cassees_examens
FROM dossier_sessions ds
LEFT JOIN examens e ON ds.examen_id = e.id
WHERE ds.examen_id IS NOT NULL
  AND e.id IS NULL;

-- Devrait être 0

-- ============================================================================
-- SECTION 4: VALIDATION DES REQUÊTES POWER BI
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 4.1 Tester la requête principale (Fait_DossiersSessions)
-- ----------------------------------------------------------------------------
-- Cette requête doit retourner des résultats
SELECT
    COUNT(*) AS total_dossiers,
    COUNT(DISTINCT examen_id) AS nombre_examens,
    COUNT(DISTINCT annexe_id) AS nombre_annexes,
    MIN(date_inscription) AS premiere_inscription,
    MAX(date_inscription) AS derniere_inscription
FROM dossier_sessions
WHERE state = 'validate'
  AND abandoned = false;

-- ----------------------------------------------------------------------------
-- 4.2 Tester les calculs du Rapport Synthétique
-- ----------------------------------------------------------------------------
SELECT
    'Inscrits Code' AS categorie,
    COUNT(*) AS total
FROM dossier_sessions
WHERE state = 'validate'
  AND abandoned = false
  AND type_examen = 'code-conduite'

UNION ALL

SELECT
    'Admis Code',
    COUNT(*)
FROM dossier_sessions
WHERE state = 'validate'
  AND abandoned = false
  AND resultat_code = 'success'

UNION ALL

SELECT
    'Admis Définitifs',
    COUNT(*)
FROM dossier_sessions
WHERE state = 'validate'
  AND abandoned = false
  AND resultat_code = 'success'
  AND resultat_conduite = 'success';

-- ----------------------------------------------------------------------------
-- 4.3 Tester les agrégations par catégorie
-- ----------------------------------------------------------------------------
SELECT
    cp.name AS categorie_permis,
    COUNT(*) AS total_candidats,
    SUM(CASE WHEN ds.resultat_code = 'success' THEN 1 ELSE 0 END) AS admis_code,
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis_definitifs,
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) / COUNT(*), 2) AS taux_reussite
FROM dossier_sessions ds
INNER JOIN categorie_permis cp ON ds.categorie_permis_id = cp.id
WHERE ds.state = 'validate'
  AND ds.abandoned = false
GROUP BY cp.id, cp.name
ORDER BY total_candidats DESC;

-- ----------------------------------------------------------------------------
-- 4.4 Tester les graphiques temporels
-- ----------------------------------------------------------------------------
SELECT
    TO_CHAR(date_inscription, 'YYYY-MM') AS mois,
    COUNT(*) AS total_inscriptions,
    SUM(CASE WHEN resultat_code = 'success' THEN 1 ELSE 0 END) AS admis_code,
    SUM(CASE WHEN resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis_definitifs
FROM dossier_sessions
WHERE state = 'validate'
  AND abandoned = false
  AND date_inscription >= DATE_TRUNC('year', CURRENT_DATE - INTERVAL '1 year')
GROUP BY TO_CHAR(date_inscription, 'YYYY-MM')
ORDER BY mois;

-- ============================================================================
-- SECTION 5: VALIDATION DES PERFORMANCES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 5.1 Vérifier les index existants
-- ----------------------------------------------------------------------------
SELECT
    schemaname,
    tablename,
    indexname,
    indexdef
FROM pg_indexes
WHERE schemaname = 'public'
  AND tablename = 'dossier_sessions'
ORDER BY tablename, indexname;

-- ----------------------------------------------------------------------------
-- 5.2 Analyser le plan d'exécution d'une requête lourde
-- ----------------------------------------------------------------------------
EXPLAIN ANALYZE
SELECT
    cp.name,
    COUNT(*) AS total,
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis
FROM dossier_sessions ds
INNER JOIN categorie_permis cp ON ds.categorie_permis_id = cp.id
WHERE ds.state = 'validate'
  AND ds.abandoned = false
GROUP BY cp.id, cp.name;

-- Analyser le temps d'exécution et les "Seq Scan" (à éviter)

-- ----------------------------------------------------------------------------
-- 5.3 Vérifier la taille des tables
-- ----------------------------------------------------------------------------
SELECT
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS taille_totale,
    pg_size_pretty(pg_relation_size(schemaname||'.'||tablename)) AS taille_table,
    pg_size_pretty(pg_indexes_size(schemaname||'.'||tablename)) AS taille_index
FROM pg_tables
WHERE schemaname = 'public'
  AND tablename IN (
      'dossier_sessions',
      'examens',
      'candidats',
      'auto_ecoles'
  )
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;

-- ============================================================================
-- SECTION 6: STATISTIQUES GLOBALES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 6.1 Rapport de Validation Complet
-- ----------------------------------------------------------------------------
SELECT
    '=== RAPPORT DE VALIDATION SIGPCB - POWER BI ===' AS rapport;

SELECT
    'Total Candidats' AS metrique,
    COUNT(*) AS valeur
FROM dossier_sessions
WHERE state = 'validate' AND abandoned = false

UNION ALL

SELECT
    'Total Examens',
    COUNT(DISTINCT examen_id)
FROM dossier_sessions
WHERE state = 'validate' AND abandoned = false

UNION ALL

SELECT
    'Total Auto-Écoles',
    COUNT(DISTINCT auto_ecole_id)
FROM dossier_sessions
WHERE state = 'validate' AND abandoned = false

UNION ALL

SELECT
    'Admis Définitifs',
    COUNT(*)
FROM dossier_sessions
WHERE state = 'validate'
  AND abandoned = false
  AND resultat_code = 'success'
  AND resultat_conduite = 'success'

UNION ALL

SELECT
    'Taux Réussite Global (%)',
    ROUND(100.0 * COUNT(CASE WHEN resultat_code = 'success' AND resultat_conduite = 'success' THEN 1 END) / COUNT(*), 2)
FROM dossier_sessions
WHERE state = 'validate' AND abandoned = false;

-- ----------------------------------------------------------------------------
-- 6.2 Dernières Mises à Jour
-- ----------------------------------------------------------------------------
SELECT
    'Dernière Inscription' AS evenement,
    MAX(date_inscription) AS date,
    COUNT(*) AS nombre_aujourdhui
FROM dossier_sessions
WHERE DATE(date_inscription) = CURRENT_DATE

UNION ALL

SELECT
    'Dernier Examen',
    MAX(date_code),
    COUNT(*)
FROM examens
WHERE DATE(date_code) = CURRENT_DATE

UNION ALL

SELECT
    'Dernière MAJ dossier_sessions',
    MAX(updated_at),
    COUNT(*)
FROM dossier_sessions
WHERE DATE(updated_at) = CURRENT_DATE;

-- ============================================================================
-- SECTION 7: CRÉATION D'INDEX RECOMMANDÉS (SI MANQUANTS)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 7.1 Créer les index pour optimiser les performances Power BI
-- ----------------------------------------------------------------------------
-- ATTENTION: Exécuter seulement si les index n'existent pas encore

-- Index pour les filtres fréquents
CREATE INDEX IF NOT EXISTS idx_ds_state_abandoned
ON dossier_sessions(state, abandoned);

CREATE INDEX IF NOT EXISTS idx_ds_examen_annexe
ON dossier_sessions(examen_id, annexe_id);

CREATE INDEX IF NOT EXISTS idx_ds_type_examen
ON dossier_sessions(type_examen);

-- Index pour les résultats
CREATE INDEX IF NOT EXISTS idx_ds_resultats
ON dossier_sessions(resultat_code, resultat_conduite);

CREATE INDEX IF NOT EXISTS idx_ds_presence
ON dossier_sessions(presence, presence_conduite);

-- Index pour les jointures
CREATE INDEX IF NOT EXISTS idx_ds_langue_id
ON dossier_sessions(langue_id);

CREATE INDEX IF NOT EXISTS idx_ds_categorie_permis_id
ON dossier_sessions(categorie_permis_id);

CREATE INDEX IF NOT EXISTS idx_ds_auto_ecole_id
ON dossier_sessions(auto_ecole_id);

CREATE INDEX IF NOT EXISTS idx_ds_npi
ON dossier_sessions(npi);

-- Index pour les dates
CREATE INDEX IF NOT EXISTS idx_ds_date_inscription
ON dossier_sessions(date_inscription);

-- Index composite pour les rapports
CREATE INDEX IF NOT EXISTS idx_ds_stats_composite
ON dossier_sessions(state, abandoned, examen_id, type_examen, resultat_code, resultat_conduite);

-- ============================================================================
-- SECTION 8: MAINTENANCE ET NETTOYAGE
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 8.1 Vacuum et Analyse (à exécuter régulièrement)
-- ----------------------------------------------------------------------------
-- VACUUM ANALYZE dossier_sessions;
-- VACUUM ANALYZE examens;
-- VACUUM ANALYZE candidats;
-- VACUUM ANALYZE auto_ecoles;

-- ----------------------------------------------------------------------------
-- 8.2 Mettre à jour les statistiques
-- ----------------------------------------------------------------------------
-- ANALYZE dossier_sessions;
-- ANALYZE examens;

-- ============================================================================
-- SECTION 9: TESTS DE CHARGE
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 9.1 Test de performance - Requête lourde
-- ----------------------------------------------------------------------------
\timing on

-- Test 1: Agrégation complexe
SELECT
    cp.name AS categorie,
    l.name AS langue,
    c.sexe,
    COUNT(*) AS total,
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis
FROM dossier_sessions ds
INNER JOIN categorie_permis cp ON ds.categorie_permis_id = cp.id
INNER JOIN langues l ON ds.langue_id = l.id
INNER JOIN candidats c ON ds.npi = c.npi
WHERE ds.state = 'validate'
  AND ds.abandoned = false
GROUP BY cp.name, l.name, c.sexe
ORDER BY total DESC;

-- Noter le temps d'exécution
-- Objectif: < 5 secondes

\timing off

-- ============================================================================
-- SECTION 10: CHECKLIST DE VALIDATION
-- ============================================================================

-- Exécuter cette requête pour obtenir un résumé de la validation
SELECT
    '✅ Validation Complète' AS statut,
    CURRENT_TIMESTAMP AS date_validation;

-- ============================================================================
-- FIN DES SCRIPTS DE VALIDATION
-- ============================================================================

/*
INSTRUCTIONS D'UTILISATION:

1. Connecter à PostgreSQL:
   psql -h [SERVEUR] -d sigpcb_base -U [USERNAME]

2. Exécuter ce fichier:
   \i SIGPCB_PowerBI_Scripts_Validation.sql

3. Analyser les résultats:
   - Vérifier qu'aucune erreur n'est remontée
   - Vérifier que les compteurs sont cohérents
   - Vérifier que les clés étrangères cassées = 0
   - Noter le temps d'exécution des requêtes lourdes

4. Si nécessaire, créer les index manquants (Section 7)

5. Planifier l'exécution régulière de VACUUM ANALYZE (Section 8)

6. Documenter les résultats avant d'importer dans Power BI

CRITÈRES DE SUCCÈS:
✅ Toutes les tables requises existent
✅ Aucune clé étrangère cassée
✅ Moins de 1% de valeurs NULL dans les colonnes importantes
✅ Aucun doublon de (NPI, examen_id)
✅ Temps d'exécution des requêtes < 5 secondes
✅ Index recommandés créés

Si tous les critères sont remplis, vous pouvez procéder à l'importation dans Power BI.
*/
