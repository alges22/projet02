-- ============================================================================
-- SIGPCB - REQUÊTES SQL POUR POWER BI
-- Système Intégré de Gestion du Permis de Conduire au Bénin
-- ============================================================================
-- Date de création: 2025-12-01
-- Base de données: PostgreSQL
-- Usage: Tableaux de bord Power BI avec rafraîchissement périodique
-- ============================================================================

-- ============================================================================
-- SECTION 1: TABLES DE DIMENSION (LOOKUP TABLES)
-- ============================================================================
-- Ces tables doivent être importées en mode "Importer" dans Power BI
-- Rafraîchissement: Quotidien ou hebdomadaire

-- ----------------------------------------------------------------------------
-- 1.1 Dimension Langues
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Dim_Langues
SELECT
    id AS langue_id,
    name AS langue_name,
    code AS langue_code,
    created_at,
    updated_at
FROM langues
ORDER BY name;

-- ----------------------------------------------------------------------------
-- 1.2 Dimension Catégories de Permis
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Dim_CategoriesPermis
SELECT
    id AS categorie_permis_id,
    name AS categorie_permis_name,
    description,
    is_extension,
    created_at,
    updated_at
FROM categorie_permis
ORDER BY name;

-- ----------------------------------------------------------------------------
-- 1.3 Dimension Auto-Écoles
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Dim_AutoEcoles
SELECT
    id AS auto_ecole_id,
    name AS auto_ecole_name,
    email,
    phone,
    created_at,
    updated_at
FROM auto_ecoles
ORDER BY name;

-- ----------------------------------------------------------------------------
-- 1.4 Dimension Annexes ANaTT (Centres d'Examen)
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Dim_Annexes
SELECT
    id AS annexe_id,
    name AS annexe_name,
    code AS annexe_code,
    ville,
    created_at,
    updated_at
FROM annexe_anatts
ORDER BY name;

-- ----------------------------------------------------------------------------
-- 1.5 Dimension Examens
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Dim_Examens
SELECT
    id AS examen_id,
    libelle AS examen_libelle,
    date_code,
    date_conduite,
    date_limite_depot,
    date_etude_dossier,
    date_convocation,
    state AS examen_state,
    EXTRACT(YEAR FROM date_code) AS annee_examen,
    TO_CHAR(date_code, 'YYYY-MM') AS mois_examen,
    TO_CHAR(date_code, 'YYYY-Q') AS trimestre_examen,
    created_at,
    updated_at
FROM examens
ORDER BY date_code DESC;

-- ----------------------------------------------------------------------------
-- 1.6 Dimension Candidats (via ANIP - si disponible localement)
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Dim_Candidats
-- Note: Si les données candidats sont dans une base séparée, ajuster la connexion
SELECT
    npi,
    nom,
    prenom,
    sexe,
    date_naissance,
    lieu_naissance,
    nationalite,
    created_at,
    updated_at
FROM candidats
ORDER BY npi;

-- ----------------------------------------------------------------------------
-- 1.7 Dimension Calendrier (Table de dates)
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Dim_Calendrier
-- Cette requête génère une table de dates pour faciliter l'analyse temporelle
WITH RECURSIVE date_range AS (
    SELECT
        DATE '2020-01-01' AS date_value
    UNION ALL
    SELECT
        date_value + INTERVAL '1 day'
    FROM date_range
    WHERE date_value < DATE '2030-12-31'
)
SELECT
    date_value AS date,
    EXTRACT(YEAR FROM date_value) AS annee,
    EXTRACT(MONTH FROM date_value) AS mois_numero,
    TO_CHAR(date_value, 'Month') AS mois_nom,
    TO_CHAR(date_value, 'Mon') AS mois_court,
    EXTRACT(QUARTER FROM date_value) AS trimestre,
    'T' || EXTRACT(QUARTER FROM date_value) AS trimestre_nom,
    EXTRACT(WEEK FROM date_value) AS semaine_numero,
    EXTRACT(DOW FROM date_value) AS jour_semaine_numero,
    TO_CHAR(date_value, 'Day') AS jour_semaine_nom,
    TO_CHAR(date_value, 'Dy') AS jour_semaine_court,
    EXTRACT(DAY FROM date_value) AS jour_mois,
    TO_CHAR(date_value, 'YYYY-MM') AS annee_mois,
    TO_CHAR(date_value, 'YYYY-Q') AS annee_trimestre,
    CASE
        WHEN EXTRACT(DOW FROM date_value) IN (0, 6) THEN 'Week-end'
        ELSE 'Semaine'
    END AS type_jour
FROM date_range;

-- ============================================================================
-- SECTION 2: TABLE DE FAITS PRINCIPALE
-- ============================================================================
-- Cette table doit être importée en mode "Importer" avec rafraîchissement périodique
-- Rafraîchissement recommandé: Toutes les heures ou quotidien selon besoin

-- ----------------------------------------------------------------------------
-- 2.1 Fait Principal - Dossiers Sessions (Vue complète)
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Fait_DossiersSessions
SELECT
    -- Clés primaires et étrangères
    ds.id AS dossier_session_id,
    ds.npi,
    ds.examen_id,
    ds.annexe_id,
    ds.langue_id,
    ds.auto_ecole_id,
    ds.categorie_permis_id,
    ds.dossier_candidat_id,

    -- Informations d'examen
    ds.type_examen,
    ds.state AS dossier_state,
    ds.abandoned,
    ds.closed,
    ds.is_paid,

    -- Résultats Code
    ds.resultat_code,
    ds.presence,
    CASE
        WHEN ds.resultat_code = 'success' THEN 1
        ELSE 0
    END AS code_reussi,
    CASE
        WHEN ds.resultat_code = 'failed' THEN 1
        ELSE 0
    END AS code_echoue,
    CASE
        WHEN ds.presence = 'present' THEN 1
        ELSE 0
    END AS code_present,
    CASE
        WHEN ds.presence = 'abscent' THEN 1
        ELSE 0
    END AS code_absent,

    -- Résultats Conduite
    ds.resultat_conduite,
    ds.presence_conduite,
    CASE
        WHEN ds.resultat_conduite = 'success' THEN 1
        ELSE 0
    END AS conduite_reussi,
    CASE
        WHEN ds.resultat_conduite = 'failed' THEN 1
        ELSE 0
    END AS conduite_echoue,
    CASE
        WHEN ds.presence_conduite = 'present' THEN 1
        ELSE 0
    END AS conduite_present,
    CASE
        WHEN ds.presence_conduite IN ('absent', 'abscent') THEN 1
        ELSE 0
    END AS conduite_absent,

    -- Admis définitif
    CASE
        WHEN ds.resultat_code = 'success'
         AND ds.resultat_conduite = 'success' THEN 1
        ELSE 0
    END AS admis_definitif,

    -- Type d'inscription
    CASE
        WHEN ds.type_examen = 'code-conduite' THEN 'Nouvelle inscription'
        WHEN ds.type_examen = 'conduite' THEN 'Reconduit'
        ELSE 'Autre'
    END AS type_inscription,

    -- Informations de paiement
    ds.montant_paiement,
    CAST(ds.montant_paiement AS NUMERIC) AS montant_paiement_numeric,

    -- Extensions et prérequis
    ds.permis_prealable_id,
    ds.permis_extension_id,

    -- Dates
    ds.date_inscription,
    ds.date_payment,
    ds.date_validation,
    EXTRACT(YEAR FROM ds.date_inscription) AS annee_inscription,
    EXTRACT(MONTH FROM ds.date_inscription) AS mois_inscription,
    TO_CHAR(ds.date_inscription, 'YYYY-MM') AS annee_mois_inscription,
    TO_CHAR(ds.date_inscription, 'YYYY-Q') AS annee_trimestre_inscription,

    -- Métadonnées
    ds.created_at,
    ds.updated_at,

    -- Statut global
    CASE
        WHEN ds.state = 'validate' AND ds.abandoned = false THEN 'Actif'
        WHEN ds.abandoned = true THEN 'Abandonné'
        WHEN ds.closed = true THEN 'Clôturé'
        ELSE 'En cours'
    END AS statut_global

FROM dossier_sessions ds
WHERE ds.state = 'validate'  -- Filtre par défaut pour les dossiers validés
ORDER BY ds.id DESC;

-- ----------------------------------------------------------------------------
-- 2.2 Fait Principal - Vue Simplifiée pour Statistiques
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Fait_DossiersSessions_Stats
-- Cette vue est optimisée pour les calculs statistiques rapides
SELECT
    ds.id AS dossier_session_id,
    ds.npi,
    ds.examen_id,
    ds.annexe_id,
    ds.langue_id,
    ds.auto_ecole_id,
    ds.categorie_permis_id,
    ds.type_examen,
    ds.resultat_code,
    ds.resultat_conduite,
    ds.presence,
    ds.presence_conduite,
    ds.date_inscription,
    EXTRACT(YEAR FROM ds.date_inscription) AS annee,

    -- Flags pour calculs rapides
    CASE WHEN ds.type_examen = 'code-conduite' THEN 1 ELSE 0 END AS inscrit_code,
    CASE WHEN ds.resultat_code = 'success' THEN 1 ELSE 0 END AS admis_code,
    CASE WHEN ds.resultat_code = 'failed' THEN 1 ELSE 0 END AS recale_code,
    CASE WHEN ds.presence = 'abscent' THEN 1 ELSE 0 END AS absent_code,
    CASE WHEN ds.presence = 'present' THEN 1 ELSE 0 END AS present_code,
    CASE WHEN ds.type_examen = 'conduite' THEN 1 ELSE 0 END AS reconduit,
    CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END AS admis_conduite,
    CASE WHEN ds.resultat_conduite = 'failed' THEN 1 ELSE 0 END AS recale_conduite,
    CASE WHEN ds.presence_conduite IN ('absent', 'abscent') THEN 1 ELSE 0 END AS absent_conduite,
    CASE WHEN ds.presence_conduite = 'present' THEN 1 ELSE 0 END AS present_conduite,
    CASE WHEN ds.resultat_code = 'success' AND ds.resultat_conduite = 'success' THEN 1 ELSE 0 END AS admis_definitif

FROM dossier_sessions ds
WHERE ds.state = 'validate'
  AND ds.abandoned = false;

-- ============================================================================
-- SECTION 3: REQUÊTES PARAMÉTRÉES POUR RAPPORTS SPÉCIFIQUES
-- ============================================================================
-- Ces requêtes utilisent des paramètres Power BI

-- ----------------------------------------------------------------------------
-- 3.1 Rapport Synthétique - Données Détaillées
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Rapport_Synthetique
-- Paramètres Power BI: @ExamenId, @AnnexeId (optionnels)
-- Note: Créer ces paramètres dans Power BI avec valeurs NULL par défaut

SELECT
    ds.id,
    ds.npi,
    ds.examen_id,
    ds.annexe_id,
    ds.langue_id,
    ds.categorie_permis_id,
    ds.type_examen,
    ds.resultat_code,
    ds.resultat_conduite,
    ds.presence,
    ds.presence_conduite,

    -- Catégories du rapport synthétique
    CASE
        WHEN ds.type_examen = 'code-conduite' THEN 'Inscrits au Code'
        ELSE NULL
    END AS categorie_1_inscrit_code,

    CASE
        WHEN ds.type_examen = 'code-conduite'
         AND ds.presence = 'abscent' THEN 'Absents au Code'
        ELSE NULL
    END AS categorie_2_absent_code,

    CASE
        WHEN ds.type_examen = 'code-conduite'
         AND ds.presence = 'present' THEN 'Présents au Code'
        ELSE NULL
    END AS categorie_3_present_code,

    CASE
        WHEN ds.resultat_code = 'failed' THEN 'Échoués au Code'
        ELSE NULL
    END AS categorie_4_echoue_code,

    CASE
        WHEN ds.resultat_code = 'success' THEN 'Admis au Code'
        ELSE NULL
    END AS categorie_5_admis_code,

    CASE
        WHEN ds.type_examen = 'conduite' THEN 'Reconduits'
        ELSE NULL
    END AS categorie_6_reconduits,

    CASE
        WHEN ds.resultat_conduite = 'failed'
         AND ds.presence_conduite IN ('absent', 'abscent') THEN 'Absents Conduite'
        ELSE NULL
    END AS categorie_7_absent_conduite,

    CASE
        WHEN ds.presence_conduite = 'present' THEN 'Présents Conduite'
        ELSE NULL
    END AS categorie_8_present_conduite,

    CASE
        WHEN ds.resultat_conduite = 'failed'
         AND ds.resultat_code = 'success' THEN 'Échoués Conduite'
        ELSE NULL
    END AS categorie_9_echoue_conduite,

    CASE
        WHEN ds.resultat_conduite = 'success' THEN 'Admis Définitifs'
        ELSE NULL
    END AS categorie_10_admis_definitifs

FROM dossier_sessions ds
WHERE ds.state = 'validate'
  AND ds.abandoned = false
  -- Utiliser des paramètres Power BI pour filtrer dynamiquement
  -- AND (@ExamenId IS NULL OR ds.examen_id = @ExamenId)
  -- AND (@AnnexeId IS NULL OR ds.annexe_id = @AnnexeId)
;

-- ----------------------------------------------------------------------------
-- 3.2 Rapport d'Activités - Par Catégorie de Permis
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Rapport_Activites_Code
SELECT
    cp.id AS categorie_permis_id,
    cp.name AS categorie_permis,
    ds.examen_id,

    -- Compteurs Code
    COUNT(*) AS total_inscrits,
    SUM(CASE WHEN ds.presence = 'present' THEN 1 ELSE 0 END) AS presents,
    SUM(CASE WHEN ds.presence = 'abscent' THEN 1 ELSE 0 END) AS absents,
    SUM(CASE WHEN ds.resultat_code = 'success' THEN 1 ELSE 0 END) AS admis,
    SUM(CASE WHEN ds.resultat_code = 'failed' THEN 1 ELSE 0 END) AS echoues,

    -- Pourcentages
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_code = 'success' THEN 1 ELSE 0 END) /
          NULLIF(COUNT(*), 0), 2) AS taux_reussite_pct

FROM dossier_sessions ds
INNER JOIN categorie_permis cp ON ds.categorie_permis_id = cp.id
WHERE ds.state = 'validate'
  AND ds.abandoned = false
  AND ds.type_examen = 'code-conduite'
  -- AND (@ExamenId IS NULL OR ds.examen_id = @ExamenId)
GROUP BY cp.id, cp.name, ds.examen_id
ORDER BY cp.name;

-- ----------------------------------------------------------------------------
-- 3.3 Rapport d'Activités - Conduite par Catégorie
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Rapport_Activites_Conduite
SELECT
    cp.id AS categorie_permis_id,
    cp.name AS categorie_permis,
    ds.examen_id,

    -- Compteurs Conduite
    COUNT(*) AS total_inscrits,
    SUM(CASE WHEN ds.presence_conduite = 'present' THEN 1 ELSE 0 END) AS presents,
    SUM(CASE WHEN ds.presence_conduite IN ('absent', 'abscent') THEN 1 ELSE 0 END) AS absents,
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis,
    SUM(CASE WHEN ds.resultat_conduite = 'failed' THEN 1 ELSE 0 END) AS echoues,

    -- Pourcentages
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) /
          NULLIF(COUNT(*), 0), 2) AS taux_reussite_pct

FROM dossier_sessions ds
INNER JOIN categorie_permis cp ON ds.categorie_permis_id = cp.id
WHERE ds.state = 'validate'
  AND ds.abandoned = false
  AND cp.is_extension = false
  -- AND (@ExamenId IS NULL OR ds.examen_id = @ExamenId)
GROUP BY cp.id, cp.name, ds.examen_id
ORDER BY cp.name;

-- ============================================================================
-- SECTION 4: REQUÊTES POUR GRAPHIQUES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 4.1 Données pour Graphique par Catégorie de Permis
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Graph_CategoriePermis
SELECT
    cp.name AS categorie_permis,
    ds.examen_id,

    -- Métriques
    COUNT(*) AS total_inscriptions,
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis_definitifs,
    SUM(CASE WHEN ds.resultat_conduite = 'failed' THEN 1 ELSE 0 END) AS recales_definitifs,

    -- Taux
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) /
          NULLIF(COUNT(*), 0), 2) AS taux_reussite

FROM dossier_sessions ds
INNER JOIN categorie_permis cp ON ds.categorie_permis_id = cp.id
WHERE ds.state = 'validate'
  AND ds.abandoned = false
GROUP BY cp.name, ds.examen_id
ORDER BY cp.name;

-- ----------------------------------------------------------------------------
-- 4.2 Données pour Graphique par Langue
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Graph_Langue
SELECT
    l.name AS langue,
    ds.examen_id,

    -- Métriques
    COUNT(*) AS total_inscriptions,
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis_definitifs,
    SUM(CASE WHEN ds.resultat_conduite = 'failed' THEN 1 ELSE 0 END) AS recales_definitifs,

    -- Taux
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) /
          NULLIF(COUNT(*), 0), 2) AS taux_reussite

FROM dossier_sessions ds
INNER JOIN langues l ON ds.langue_id = l.id
WHERE ds.state = 'validate'
  AND ds.abandoned = false
GROUP BY l.name, ds.examen_id
ORDER BY l.name;

-- ----------------------------------------------------------------------------
-- 4.3 Données pour Graphique par Sexe
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Graph_Sexe
-- Note: Nécessite jointure avec table candidats
SELECT
    c.sexe,
    ds.examen_id,

    -- Métriques
    COUNT(*) AS total_inscriptions,
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis_definitifs,
    SUM(CASE WHEN ds.resultat_conduite = 'failed' THEN 1 ELSE 0 END) AS recales_definitifs,

    -- Taux
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) /
          NULLIF(COUNT(*), 0), 2) AS taux_reussite

FROM dossier_sessions ds
INNER JOIN candidats c ON ds.npi = c.npi
WHERE ds.state = 'validate'
  AND ds.abandoned = false
GROUP BY c.sexe, ds.examen_id
ORDER BY c.sexe;

-- ----------------------------------------------------------------------------
-- 4.4 Données pour Graphique par Annexe
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Graph_Annexe
SELECT
    a.name AS annexe,
    ds.examen_id,

    -- Métriques
    COUNT(*) AS total_inscriptions,
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis_definitifs,
    SUM(CASE WHEN ds.resultat_conduite = 'failed' THEN 1 ELSE 0 END) AS recales_definitifs,

    -- Taux
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) /
          NULLIF(COUNT(*), 0), 2) AS taux_reussite

FROM dossier_sessions ds
INNER JOIN annexe_anatts a ON ds.annexe_id = a.id
WHERE ds.state = 'validate'
  AND ds.abandoned = false
GROUP BY a.name, ds.examen_id
ORDER BY a.name;

-- ----------------------------------------------------------------------------
-- 4.5 Évolution Temporelle (Série Chronologique)
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Graph_Evolution_Temporelle
SELECT
    DATE_TRUNC('month', ds.date_inscription) AS mois,
    TO_CHAR(ds.date_inscription, 'YYYY-MM') AS annee_mois,

    -- Métriques mensuelles
    COUNT(*) AS total_inscriptions,
    SUM(CASE WHEN ds.type_examen = 'code-conduite' THEN 1 ELSE 0 END) AS nouvelles_inscriptions,
    SUM(CASE WHEN ds.type_examen = 'conduite' THEN 1 ELSE 0 END) AS reconduits,
    SUM(CASE WHEN ds.resultat_code = 'success' THEN 1 ELSE 0 END) AS admis_code,
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis_definitifs,

    -- Taux de réussite mensuel
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) /
          NULLIF(COUNT(*), 0), 2) AS taux_reussite

FROM dossier_sessions ds
WHERE ds.state = 'validate'
  AND ds.abandoned = false
  AND ds.date_inscription >= DATE_TRUNC('year', CURRENT_DATE - INTERVAL '2 years')
GROUP BY DATE_TRUNC('month', ds.date_inscription), TO_CHAR(ds.date_inscription, 'YYYY-MM')
ORDER BY mois;

-- ============================================================================
-- SECTION 5: COMPTEURS POUR TABLEAU DE BORD
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 5.1 Compteurs Principaux - Vue Unique
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Compteurs_Principaux
SELECT
    'Utilisateurs' AS compteur_type,
    COUNT(*) AS compteur_valeur
FROM users
UNION ALL
SELECT 'Auto-Écoles', COUNT(*) FROM auto_ecoles
UNION ALL
SELECT 'Licences Actives', COUNT(*)
FROM licences WHERE date_fin >= CURRENT_DATE
UNION ALL
SELECT 'Licences Expirées', COUNT(*)
FROM licences WHERE date_fin < CURRENT_DATE
UNION ALL
SELECT 'Inspecteurs', COUNT(*) FROM inspecteurs
UNION ALL
SELECT 'Examinateurs', COUNT(*) FROM examinateurs
UNION ALL
SELECT 'Annexes ANaTT', COUNT(*) FROM annexe_anatts;

-- ----------------------------------------------------------------------------
-- 5.2 Compteurs E-Services
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Compteurs_EServices
SELECT
    'Authenticité' AS service,
    'En attente' AS statut,
    COUNT(*) AS nombre
FROM authenticites WHERE state IN ('init', 'pending')
UNION ALL
SELECT 'Authenticité', 'Rejetés', COUNT(*)
FROM authenticites WHERE state = 'rejected'
UNION ALL
SELECT 'Authenticité', 'Validés', COUNT(*)
FROM authenticites WHERE state = 'validate'

UNION ALL
SELECT 'Permis International', 'En attente', COUNT(*)
FROM permis_internationals WHERE state IN ('init', 'pending')
UNION ALL
SELECT 'Permis International', 'Rejetés', COUNT(*)
FROM permis_internationals WHERE state = 'rejected'
UNION ALL
SELECT 'Permis International', 'Validés', COUNT(*)
FROM permis_internationals WHERE state = 'validate'

UNION ALL
SELECT 'Duplicata', 'En attente', COUNT(*)
FROM duplicatas WHERE state IN ('init', 'pending')
UNION ALL
SELECT 'Duplicata', 'Rejetés', COUNT(*)
FROM duplicatas WHERE state = 'rejected'
UNION ALL
SELECT 'Duplicata', 'Validés', COUNT(*)
FROM duplicatas WHERE state = 'validate'

UNION ALL
SELECT 'Échange', 'En attente', COUNT(*)
FROM echanges WHERE state IN ('init', 'pending')
UNION ALL
SELECT 'Échange', 'Rejetés', COUNT(*)
FROM echanges WHERE state = 'rejected'
UNION ALL
SELECT 'Échange', 'Validés', COUNT(*)
FROM echanges WHERE state = 'validate'

UNION ALL
SELECT 'Prorogation', 'En attente', COUNT(*)
FROM prorogations WHERE state IN ('init', 'pending')
UNION ALL
SELECT 'Prorogation', 'Rejetés', COUNT(*)
FROM prorogations WHERE state = 'rejected'
UNION ALL
SELECT 'Prorogation', 'Validés', COUNT(*)
FROM prorogations WHERE state = 'validate';

-- ----------------------------------------------------------------------------
-- 5.3 Compteurs Auto-Écoles
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Compteurs_AutoEcoles
SELECT
    'Demande Agrément' AS type_demande,
    'Nouvelles' AS statut,
    COUNT(*) AS nombre
FROM demande_agrements WHERE state IN ('init', 'pending')
UNION ALL
SELECT 'Demande Agrément', 'Rejetées', COUNT(*)
FROM demande_agrements WHERE state = 'rejected'
UNION ALL
SELECT 'Demande Agrément', 'Validées', COUNT(*)
FROM demande_agrements WHERE state = 'validate'

UNION ALL
SELECT 'Demande Licence', 'Nouvelles', COUNT(*)
FROM demande_licences WHERE state IN ('init', 'pending')
UNION ALL
SELECT 'Demande Licence', 'Rejetées', COUNT(*)
FROM demande_licences WHERE state = 'rejected'
UNION ALL
SELECT 'Demande Licence', 'Validées', COUNT(*)
FROM demande_licences WHERE state = 'validate';

-- ----------------------------------------------------------------------------
-- 5.4 Compteurs Recrutement
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Compteurs_Recrutement
SELECT
    'Examinateurs' AS type_recrutement,
    'En attente' AS statut,
    COUNT(*) AS nombre
FROM demande_examinateurs WHERE state IN ('init', 'pending')
UNION ALL
SELECT 'Examinateurs', 'Rejetés', COUNT(*)
FROM demande_examinateurs WHERE state = 'rejected'
UNION ALL
SELECT 'Examinateurs', 'Validés', COUNT(*)
FROM demande_examinateurs WHERE state = 'validate'

UNION ALL
SELECT 'Moniteurs', 'En attente', COUNT(*)
FROM demande_moniteurs WHERE state IN ('init', 'pending')
UNION ALL
SELECT 'Moniteurs', 'Rejetés', COUNT(*)
FROM demande_moniteurs WHERE state = 'rejected'
UNION ALL
SELECT 'Moniteurs', 'Validés', COUNT(*)
FROM demande_moniteurs WHERE state = 'validate'

UNION ALL
SELECT 'Entreprises', 'Total', COUNT(*) FROM entreprises;

-- ============================================================================
-- SECTION 6: STATISTIQUES D'EXAMENS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 6.1 Statistiques Code
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Stats_Code
SELECT
    ds.examen_id,
    ds.annexe_id,

    -- Statistiques globales
    COUNT(*) AS total_inscrits,
    SUM(CASE WHEN ds.presence = 'present' THEN 1 ELSE 0 END) AS presents,
    SUM(CASE WHEN ds.presence = 'abscent' THEN 1 ELSE 0 END) AS absents,
    SUM(CASE WHEN ds.resultat_code = 'success' THEN 1 ELSE 0 END) AS admis,
    SUM(CASE WHEN ds.resultat_code = 'failed' THEN 1 ELSE 0 END) AS recales,

    -- Pourcentages
    ROUND(100.0 * SUM(CASE WHEN ds.presence = 'present' THEN 1 ELSE 0 END) /
          NULLIF(COUNT(*), 0), 2) AS taux_presence,
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_code = 'success' THEN 1 ELSE 0 END) /
          NULLIF(SUM(CASE WHEN ds.presence = 'present' THEN 1 ELSE 0 END), 0), 2) AS taux_reussite

FROM dossier_sessions ds
WHERE ds.state = 'validate'
  AND ds.type_examen = 'code-conduite'
  -- AND (@ExamenId IS NULL OR ds.examen_id = @ExamenId)
  -- AND (@AnnexeId IS NULL OR ds.annexe_id = @AnnexeId)
GROUP BY ds.examen_id, ds.annexe_id;

-- ----------------------------------------------------------------------------
-- 6.2 Statistiques Conduite
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Stats_Conduite
SELECT
    ds.examen_id,
    ds.annexe_id,

    -- Statistiques globales
    COUNT(*) AS total_candidats,
    SUM(CASE WHEN ds.presence_conduite = 'present' THEN 1 ELSE 0 END) AS presents,
    SUM(CASE WHEN ds.presence_conduite IN ('absent', 'abscent') THEN 1 ELSE 0 END) AS absents,
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis,
    SUM(CASE WHEN ds.resultat_conduite = 'failed' THEN 1 ELSE 0 END) AS recales,

    -- Pourcentages
    ROUND(100.0 * SUM(CASE WHEN ds.presence_conduite = 'present' THEN 1 ELSE 0 END) /
          NULLIF(COUNT(*), 0), 2) AS taux_presence,
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) /
          NULLIF(SUM(CASE WHEN ds.presence_conduite = 'present' THEN 1 ELSE 0 END), 0), 2) AS taux_reussite

FROM dossier_sessions ds
WHERE ds.state = 'validate'
  AND ds.resultat_code = 'success'  -- Uniquement ceux qui ont réussi le code
  -- AND (@ExamenId IS NULL OR ds.examen_id = @ExamenId)
  -- AND (@AnnexeId IS NULL OR ds.annexe_id = @AnnexeId)
GROUP BY ds.examen_id, ds.annexe_id;

-- ============================================================================
-- SECTION 7: ANALYSES AVANCÉES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 7.1 Top 10 Auto-Écoles par Taux de Réussite
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Top_AutoEcoles_Reussite
SELECT
    ae.name AS auto_ecole,
    COUNT(*) AS total_candidats,
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis_definitifs,
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) /
          NULLIF(COUNT(*), 0), 2) AS taux_reussite

FROM dossier_sessions ds
INNER JOIN auto_ecoles ae ON ds.auto_ecole_id = ae.id
WHERE ds.state = 'validate'
  AND ds.abandoned = false
GROUP BY ae.id, ae.name
HAVING COUNT(*) >= 10  -- Minimum 10 candidats
ORDER BY taux_reussite DESC
LIMIT 10;

-- ----------------------------------------------------------------------------
-- 7.2 Analyse par Tranche d'Âge (nécessite jointure avec candidats)
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Analyse_Age
SELECT
    CASE
        WHEN EXTRACT(YEAR FROM AGE(c.date_naissance)) < 20 THEN '< 20 ans'
        WHEN EXTRACT(YEAR FROM AGE(c.date_naissance)) BETWEEN 20 AND 25 THEN '20-25 ans'
        WHEN EXTRACT(YEAR FROM AGE(c.date_naissance)) BETWEEN 26 AND 30 THEN '26-30 ans'
        WHEN EXTRACT(YEAR FROM AGE(c.date_naissance)) BETWEEN 31 AND 40 THEN '31-40 ans'
        ELSE '> 40 ans'
    END AS tranche_age,

    COUNT(*) AS total_candidats,
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis_definitifs,
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) /
          NULLIF(COUNT(*), 0), 2) AS taux_reussite

FROM dossier_sessions ds
INNER JOIN candidats c ON ds.npi = c.npi
WHERE ds.state = 'validate'
  AND ds.abandoned = false
GROUP BY tranche_age
ORDER BY tranche_age;

-- ----------------------------------------------------------------------------
-- 7.3 Analyse Comparative par Examen
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Analyse_Comparative_Examens
SELECT
    e.id AS examen_id,
    e.libelle AS examen,
    e.date_code,

    -- Métriques globales
    COUNT(DISTINCT ds.id) AS total_candidats,

    -- Code
    SUM(CASE WHEN ds.type_examen = 'code-conduite' THEN 1 ELSE 0 END) AS inscrits_code,
    SUM(CASE WHEN ds.resultat_code = 'success' THEN 1 ELSE 0 END) AS admis_code,
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_code = 'success' THEN 1 ELSE 0 END) /
          NULLIF(SUM(CASE WHEN ds.type_examen = 'code-conduite' THEN 1 ELSE 0 END), 0), 2) AS taux_reussite_code,

    -- Conduite
    SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) AS admis_definitifs,
    ROUND(100.0 * SUM(CASE WHEN ds.resultat_conduite = 'success' THEN 1 ELSE 0 END) /
          NULLIF(COUNT(*), 0), 2) AS taux_reussite_global

FROM examens e
LEFT JOIN dossier_sessions ds ON e.id = ds.examen_id AND ds.state = 'validate' AND ds.abandoned = false
WHERE e.date_code >= DATE_TRUNC('year', CURRENT_DATE - INTERVAL '1 year')
GROUP BY e.id, e.libelle, e.date_code
ORDER BY e.date_code DESC;

-- ----------------------------------------------------------------------------
-- 7.4 Permis Délivrés (avec pagination)
-- ----------------------------------------------------------------------------
-- Nom de la requête Power BI: Permis_Delivres
SELECT
    p.id AS permis_id,
    p.npi,
    p.numero_permis,
    p.categorie_permis_id,
    cp.name AS categorie_permis,
    p.delivered_at AS date_delivrance,
    EXTRACT(YEAR FROM p.delivered_at) AS annee_delivrance,
    TO_CHAR(p.delivered_at, 'YYYY-MM') AS mois_delivrance,
    p.date_validite,
    p.date_expiration,
    CASE
        WHEN p.date_expiration < CURRENT_DATE THEN 'Expiré'
        WHEN p.date_expiration < CURRENT_DATE + INTERVAL '3 months' THEN 'Bientôt expiré'
        ELSE 'Valide'
    END AS statut_permis

FROM permis p
INNER JOIN categorie_permis cp ON p.categorie_permis_id = cp.id
WHERE p.delivered_at IS NOT NULL
ORDER BY p.delivered_at DESC;

-- ============================================================================
-- FIN DU FICHIER
-- ============================================================================
