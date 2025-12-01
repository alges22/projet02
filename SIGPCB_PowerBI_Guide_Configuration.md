# 📊 GUIDE DE CONFIGURATION POWER BI - SIGPCB

## Système Intégré de Gestion du Permis de Conduire au Bénin

**Version:** 1.0
**Date:** 01 Décembre 2025
**Auteur:** Équipe SIGPCB

---

## 📋 TABLE DES MATIÈRES

1. [Prérequis](#1-prérequis)
2. [Installation et Configuration Initiale](#2-installation-et-configuration-initiale)
3. [Connexion à la Base de Données](#3-connexion-à-la-base-de-données)
4. [Importation des Tables](#4-importation-des-tables)
5. [Création du Modèle de Données](#5-création-du-modèle-de-données)
6. [Création des Mesures DAX](#6-création-des-mesures-dax)
7. [Création des Paramètres](#7-création-des-paramètres)
8. [Configuration du Rafraîchissement](#8-configuration-du-rafraîchissement)
9. [Création des Visualisations](#9-création-des-visualisations)
10. [Publication et Partage](#10-publication-et-partage)
11. [Maintenance et Optimisation](#11-maintenance-et-optimisation)
12. [Dépannage](#12-dépannage)

---

## 1. PRÉREQUIS

### 1.1 Logiciels Requis

- **Power BI Desktop** (dernière version)
  - Téléchargement: https://powerbi.microsoft.com/fr-fr/desktop/
- **Compte Power BI Pro** (pour publication)
- **Accès à la base de données PostgreSQL SIGPCB**

### 1.2 Accès Requis

- **Serveur PostgreSQL**:
  - Hôte: `[ADRESSE_SERVEUR]`
  - Port: `5432` (par défaut)
  - Base de données: `sigpcb_base`
- **Identifiants**:
  - Utilisateur: `[USERNAME]`
  - Mot de passe: `[PASSWORD]`
- **Permissions SQL**: `SELECT` sur toutes les tables requises

### 1.3 Fichiers Fournis

- ✅ `SIGPCB_PowerBI_Queries.sql` - Requêtes SQL pour Power BI
- ✅ `SIGPCB_PowerBI_Mesures_DAX.txt` - Mesures DAX
- ✅ `SIGPCB_PowerBI_Guide_Configuration.md` - Ce guide

---

## 2. INSTALLATION ET CONFIGURATION INITIALE

### 2.1 Installation de Power BI Desktop

1. Télécharger Power BI Desktop depuis le site officiel Microsoft
2. Exécuter le fichier d'installation
3. Suivre les instructions d'installation
4. Lancer Power BI Desktop

### 2.2 Configuration des Options Power BI

1. **Fichier** > **Options et paramètres** > **Options**
2. Configurer:
   - **Paramètres régionaux**: Français (France)
   - **Sécurité**: Activer les connexions chiffrées
   - **Actualisation des données**: Configurer selon besoin
3. Cliquer sur **OK**

---

## 3. CONNEXION À LA BASE DE DONNÉES

### 3.1 Créer une Nouvelle Connexion PostgreSQL

1. Dans Power BI Desktop, cliquer sur **Obtenir des données**
2. Rechercher et sélectionner **PostgreSQL**
3. Cliquer sur **Se connecter**

### 3.2 Paramètres de Connexion

```
Serveur: [ADRESSE_SERVEUR]
Base de données: sigpcb_base
```

### 3.3 Authentification

1. Sélectionner **Base de données**
2. Entrer:
   - **Nom d'utilisateur**: `[USERNAME]`
   - **Mot de passe**: `[PASSWORD]`
3. Cocher **Mémoriser mes informations d'identification**
4. Cliquer sur **Se connecter**

### 3.4 Validation de la Connexion

- Si la connexion réussit, vous verrez la liste des tables disponibles
- Si erreur, vérifier:
  - L'adresse du serveur
  - Les identifiants
  - Le pare-feu / les règles de sécurité
  - La disponibilité du serveur

---

## 4. IMPORTATION DES TABLES

### 4.1 Tables de Dimension à Importer

Importer les tables suivantes en mode **Import**:

#### ✅ **Dim_Langues**
```sql
-- Copier la requête depuis SIGPCB_PowerBI_Queries.sql (Section 1.1)
```
1. Dans le navigateur, cliquer sur **Nouvelle source** > **Requête vide**
2. Dans l'éditeur avancé, coller la requête SQL
3. Nommer la requête: `Dim_Langues`
4. Cliquer sur **Fermer et appliquer**

#### ✅ **Dim_CategoriesPermis**
```sql
-- Copier la requête depuis SIGPCB_PowerBI_Queries.sql (Section 1.2)
```

#### ✅ **Dim_AutoEcoles**
```sql
-- Copier la requête depuis SIGPCB_PowerBI_Queries.sql (Section 1.3)
```

#### ✅ **Dim_Annexes**
```sql
-- Copier la requête depuis SIGPCB_PowerBI_Queries.sql (Section 1.4)
```

#### ✅ **Dim_Examens**
```sql
-- Copier la requête depuis SIGPCB_PowerBI_Queries.sql (Section 1.5)
```

#### ✅ **Dim_Candidats** (optionnel)
```sql
-- Copier la requête depuis SIGPCB_PowerBI_Queries.sql (Section 1.6)
-- Note: Si les données candidats sont volumineuses, considérer un filtrage
```

#### ✅ **Dim_Calendrier**
```sql
-- Copier la requête depuis SIGPCB_PowerBI_Queries.sql (Section 1.7)
-- Cette table est générée et ne dépend pas de données externes
```

### 4.2 Table de Faits Principale

#### ✅ **Fait_DossiersSessions**
```sql
-- Copier la requête depuis SIGPCB_PowerBI_Queries.sql (Section 2.1)
```

**IMPORTANT**: Cette table est volumineuse. Options:
- **Option A** (Recommandée): Importer toutes les données avec filtre sur les 2 dernières années
- **Option B**: Utiliser DirectQuery pour les données temps réel (plus lent)

#### ✅ **Fait_DossiersSessions_Stats** (Vue simplifiée)
```sql
-- Copier la requête depuis SIGPCB_PowerBI_Queries.sql (Section 2.2)
-- Version optimisée pour performances
```

### 4.3 Tables pour Rapports Spécifiques

Importer selon besoin:
- `Rapport_Synthetique`
- `Rapport_Activites_Code`
- `Rapport_Activites_Conduite`
- `Graph_CategoriePermis`
- `Graph_Langue`
- `Graph_Sexe`
- `Graph_Annexe`
- `Graph_Evolution_Temporelle`
- `Compteurs_Principaux`
- `Compteurs_EServices`
- `Stats_Code`
- `Stats_Conduite`

### 4.4 Vérification des Importations

1. Aller dans **Vue Modèle**
2. Vérifier que toutes les tables apparaissent
3. Vérifier le nombre de lignes importées (en bas de l'écran)

---

## 5. CRÉATION DU MODÈLE DE DONNÉES

### 5.1 Relations entre les Tables

#### **Relations à Créer Manuellement**

Dans **Vue Modèle**, créer les relations suivantes:

```
Fait_DossiersSessions[langue_id] ──→ Dim_Langues[langue_id]
    Type: Plusieurs à un (*:1)
    Cardinalité: Plusieurs à un
    Direction du filtre croisé: Unique

Fait_DossiersSessions[categorie_permis_id] ──→ Dim_CategoriesPermis[categorie_permis_id]
    Type: Plusieurs à un (*:1)

Fait_DossiersSessions[auto_ecole_id] ──→ Dim_AutoEcoles[auto_ecole_id]
    Type: Plusieurs à un (*:1)

Fait_DossiersSessions[annexe_id] ──→ Dim_Annexes[annexe_id]
    Type: Plusieurs à un (*:1)

Fait_DossiersSessions[examen_id] ──→ Dim_Examens[examen_id]
    Type: Plusieurs à un (*:1)

Fait_DossiersSessions[npi] ──→ Dim_Candidats[npi] (si importé)
    Type: Plusieurs à un (*:1)

Fait_DossiersSessions[date_inscription] ──→ Dim_Calendrier[date]
    Type: Plusieurs à un (*:1)
```

#### **Schéma en Étoile**

```
           Dim_Langues
                │
                │
           Dim_CategoriesPermis
                │
                │
Dim_Candidats ──┼── Fait_DossiersSessions ──── Dim_Examens
                │
                │
           Dim_AutoEcoles ─── Dim_Annexes
                │
                │
           Dim_Calendrier
```

### 5.2 Masquer les Colonnes Inutiles

Dans chaque table, masquer les colonnes techniques:
- Clés étrangères (sauf si nécessaire)
- Dates de création/modification (created_at, updated_at)
- Colonnes techniques

**Méthode**: Clic droit sur la colonne > **Masquer**

### 5.3 Définir les Colonnes Clés

Pour chaque table de dimension:
1. Sélectionner la colonne ID (ex: `langue_id`)
2. **Propriétés** > **Est une clé** > **Oui**

### 5.4 Créer une Hiérarchie Temporelle

Dans **Dim_Calendrier**:
1. Clic droit sur `annee` > **Créer une hiérarchie**
2. Nommer: `Hiérarchie Temps`
3. Glisser-déposer dans l'ordre:
   - `annee`
   - `trimestre`
   - `mois_nom`
   - `date`

### 5.5 Créer des Hiérarchies Géographiques (si applicable)

Si disponible, créer:
```
Hiérarchie_Geo
├── Région
├── Département
└── Commune
```

---

## 6. CRÉATION DES MESURES DAX

### 6.1 Créer une Table de Mesures

1. **Modélisation** > **Nouvelle table**
2. Nom: `_Mesures`
3. DAX:
```dax
_Mesures = {1}
```
4. Masquer la colonne `Value`

### 6.2 Organiser les Mesures par Dossiers

Créer les dossiers suivants dans la table `_Mesures`:
- 📁 **01 - Compteurs Principaux**
- 📁 **02 - Taux et Pourcentages**
- 📁 **03 - Comparaisons Temporelles**
- 📁 **04 - Rapport Synthétique**
- 📁 **05 - Indicateurs Clés**
- 📁 **06 - Utilitaires**

### 6.3 Importer les Mesures DAX

Ouvrir le fichier `SIGPCB_PowerBI_Mesures_DAX.txt` et créer les mesures:

#### **Méthode Manuelle**:
1. Sélectionner la table `_Mesures`
2. **Modélisation** > **Nouvelle mesure**
3. Copier-coller la formule DAX
4. Renommer la mesure
5. Affecter au dossier approprié

#### **Méthode Rapide avec Tabular Editor** (recommandé):
1. Installer **Tabular Editor 2** (gratuit)
2. Ouvrir le fichier .pbix avec Tabular Editor
3. Copier-coller toutes les mesures en une fois
4. Sauvegarder

### 6.4 Formater les Mesures

Pour chaque mesure de **pourcentage**:
1. Sélectionner la mesure
2. **Format** > **Pourcentage**
3. **Décimales** > `2`

Pour les **compteurs**:
1. **Format** > **Nombre entier**
2. Activer le **séparateur de milliers**

---

## 7. CRÉATION DES PARAMÈTRES

### 7.1 Créer des Paramètres de Filtre

#### **Paramètre: Examen Sélectionné**

1. **Modélisation** > **Nouveau paramètre** > **Champs**
2. Nom: `Param_Examen`
3. Source: `Dim_Examens[examen_libelle]`
4. Valeur par défaut: `(Tous)`
5. Cliquer sur **OK**

#### **Paramètre: Année**

1. **Modélisation** > **Nouveau paramètre** > **Numérique**
2. Nom: `Param_Annee`
3. Type: **Liste de valeurs**
4. Valeurs: `2020, 2021, 2022, 2023, 2024, 2025`
5. Valeur par défaut: Année en cours
6. Cliquer sur **OK**

### 7.2 Utiliser les Paramètres dans les Visuels

Les paramètres créés peuvent être utilisés comme segments (slicers) dans les rapports.

---

## 8. CONFIGURATION DU RAFRAÎCHISSEMENT

### 8.1 Rafraîchissement Local (Power BI Desktop)

**Rafraîchissement Manuel**:
1. **Accueil** > **Actualiser**

**Rafraîchissement Automatique** (pour DirectQuery):
1. **Fichier** > **Options et paramètres** > **Options**
2. **Actualisation automatique des pages**
3. Configurer l'intervalle (ex: toutes les 30 minutes)

### 8.2 Rafraîchissement Planifié (Power BI Service)

Après publication sur Power BI Service:

#### **Configuration de la Passerelle**

1. Installer **Passerelle de données locale** sur un serveur
2. Configurer la connexion à PostgreSQL
3. Enregistrer la passerelle dans Power BI Service

#### **Planification du Rafraîchissement**

1. Dans Power BI Service, aller sur le jeu de données
2. **Paramètres** > **Actualisation planifiée**
3. Configurer:
   - **Fréquence**: Quotidienne
   - **Heure**: 06:00 (avant ouverture des bureaux)
   - **Alertes par e-mail**: Activé
4. Cliquer sur **Appliquer**

#### **Recommandations de Planification**

| Table | Fréquence Recommandée |
|-------|----------------------|
| Dim_Langues | Hebdomadaire |
| Dim_CategoriesPermis | Hebdomadaire |
| Dim_AutoEcoles | Quotidienne |
| Dim_Annexes | Mensuelle |
| Dim_Examens | Quotidienne |
| Dim_Candidats | Quotidienne (si applicable) |
| Dim_Calendrier | Annuelle |
| **Fait_DossiersSessions** | **Toutes les heures** ou **Quotidienne** |
| Compteurs | Quotidienne |

### 8.3 Optimisation du Rafraîchissement

Pour réduire le temps de rafraîchissement:
1. **Actualisation incrémentielle** (Power BI Pro):
   - Paramètres du jeu de données > Actualisation incrémentielle
   - Archiver les données de plus de 2 ans
   - Actualiser seulement les 30 derniers jours

2. **Index sur la base de données**:
   ```sql
   -- Exécuter ces commandes sur PostgreSQL pour optimiser
   CREATE INDEX idx_dossier_sessions_stats
   ON dossier_sessions(state, abandoned, examen_id, annexe_id);

   CREATE INDEX idx_dossier_sessions_results
   ON dossier_sessions(resultat_code, resultat_conduite);

   CREATE INDEX idx_dossier_sessions_date
   ON dossier_sessions(date_inscription);
   ```

---

## 9. CRÉATION DES VISUALISATIONS

### 9.1 Page 1: Vue d'Ensemble (Dashboard)

#### **Disposition**:
```
┌─────────────────────────────────────────────────────────────┐
│  SIGPCB - Tableau de Bord Principal          [Filtres]      │
├─────────────────────────────────────────────────────────────┤
│  [Total       [Admis        [Taux de        [Évolution     │
│  Candidats]   Définitifs]   Réussite]       Mensuelle]     │
├─────────────────────────────────────────────────────────────┤
│  [Graphique Évolution Temporelle]                           │
│  (Graphique en courbes - Inscriptions/Admis par mois)       │
├─────────────────────────────────────────────────────────────┤
│  [Taux Réussite   │  [Top 10 Auto-Écoles]                  │
│  par Catégorie]   │  (Graphique en barres)                 │
│  (Graphique pie)  │                                         │
└─────────────────────────────────────────────────────────────┘
```

#### **Visuels à Créer**:

1. **Carte: Total Candidats**
   - Visual: Carte
   - Champs: `[Total Candidats]`
   - Format: Couleur de fond #006F6F

2. **Carte: Admis Définitifs**
   - Visual: Carte
   - Champs: `[Total Admis Définitifs]`
   - Format: Couleur de fond #28a745

3. **Jauge: Taux de Réussite**
   - Visual: Jauge
   - Valeur: `[Taux Réussite Global]`
   - Objectif: `[Objectif Taux Réussite]`
   - Plage: 0% à 100%

4. **Graphique en Courbes: Évolution**
   - Visual: Graphique en courbes
   - Axe X: `Dim_Calendrier[annee_mois]`
   - Valeurs: `[Total Candidats]`, `[Total Admis Définitifs]`

5. **Graphique Secteurs: Réussite par Catégorie**
   - Visual: Graphique en secteurs
   - Légende: `Dim_CategoriesPermis[categorie_permis_name]`
   - Valeurs: `[Taux Réussite Global]`

6. **Graphique Barres: Top Auto-Écoles**
   - Visual: Graphique à barres empilées
   - Axe Y: `Dim_AutoEcoles[auto_ecole_name]`
   - Axe X: `[Top 10 Auto-Écoles]`
   - Tri: Par valeur décroissant
   - Limite: 10

### 9.2 Page 2: Rapport Synthétique

#### **Disposition**:
```
┌─────────────────────────────────────────────────────────────┐
│  RAPPORT SYNTHÉTIQUE                    [Filtres: Examen]   │
├─────────────────────────────────────────────────────────────┤
│  Période: [Texte Dynamique]                                 │
│  Examen: [Texte Dynamique]                                  │
├─────────────────────────────────────────────────────────────┤
│  [Tableau Rapport Synthétique - 10 Catégories]              │
│  Catégorie              │ Total │ H │ F │ %                │
│  ────────────────────────│───────│───│───│───               │
│  01. Inscrits Code      │  XXX  │...│...│...               │
│  02. Absents Code       │  XXX  │...│...│...               │
│  03. Présents Code      │  XXX  │...│...│...               │
│  ...                                                         │
└─────────────────────────────────────────────────────────────┘
```

#### **Visual: Tableau**
- Colonnes:
  - Catégorie (texte)
  - Total: `[RS_01_Inscrits Code]`, etc.
  - Hommes: filtrer par `sexe = 'M'`
  - Femmes: filtrer par `sexe = 'F'`
  - %: `[RS_01_Inscrits Code %]`, etc.

### 9.3 Page 3: Rapport d'Activités

#### **Visual: Matrice - Code**
```
Catégorie     │ Inscrits │ Présents │ Absents │ Admis │ Échoués │ Taux %
──────────────│──────────│──────────│─────────│───────│─────────│───────
A             │   XXX    │   XXX    │   XXX   │  XXX  │   XXX   │  XX%
B             │   XXX    │   XXX    │   XXX   │  XXX  │   XXX   │  XX%
...
```

#### **Visual: Matrice - Conduite**
(Même structure)

### 9.4 Page 4: Graphiques Avancés

1. **Graphique: Réussite par Langue**
   - Visual: Graphique en colonnes groupées
   - Axe X: `Dim_Langues[langue_name]`
   - Valeurs: `[Total Admis Code]`, `[Total Recalés Code]`

2. **Graphique: Réussite par Sexe**
   - Visual: Graphique en anneaux
   - Légende: `Dim_Candidats[sexe]`
   - Valeurs: `[Total Admis Définitifs]`

3. **Graphique: Réussite par Annexe**
   - Visual: Carte (map) ou Graphique en barres
   - Emplacement: `Dim_Annexes[annexe_name]`
   - Taille: `[Total Candidats]`
   - Couleur: `[Taux Réussite Global]`

### 9.5 Page 5: Compteurs et E-Services

#### **Visual: Cartes Multiples**
Créer des cartes pour:
- Nombre d'utilisateurs
- Nombre d'auto-écoles
- Licences actives/expirées
- Demandes en attente (par type)

#### **Visual: Tableau E-Services**
- Lignes: Service (Authenticité, Duplicata, etc.)
- Colonnes: En attente, Rejetés, Validés

### 9.6 Segments (Filtres)

Ajouter des segments sur chaque page:
1. **Sélecteur d'Examen**
   - Champ: `Dim_Examens[examen_libelle]`
   - Style: Liste déroulante
2. **Sélecteur d'Annexe**
   - Champ: `Dim_Annexes[annexe_name]`
   - Style: Liste
3. **Sélecteur de Période**
   - Champ: `Dim_Calendrier[annee]`
   - Style: Curseur

---

## 10. PUBLICATION ET PARTAGE

### 10.1 Publier sur Power BI Service

1. Dans Power BI Desktop:
   - **Fichier** > **Publier** > **Publier sur Power BI**
2. Sélectionner l'espace de travail de destination
3. Cliquer sur **Sélectionner**
4. Attendre la fin de la publication

### 10.2 Créer un Espace de Travail Dédié

Dans Power BI Service:
1. **Espaces de travail** > **Créer un espace de travail**
2. Nom: `SIGPCB - Tableaux de Bord`
3. Description: `Tableaux de bord et rapports du système SIGPCB`
4. Cliquer sur **Enregistrer**

### 10.3 Partager avec les Utilisateurs

#### **Attribution de Rôles**:
- **Admin**: Peut modifier et publier
- **Membre**: Peut créer du contenu
- **Contributeur**: Peut consulter et partager
- **Lecteur**: Consultation seulement

#### **Méthode**:
1. Aller dans l'espace de travail
2. **Accès** > **Ajouter des personnes**
3. Entrer les adresses e-mail
4. Sélectionner le rôle
5. Cliquer sur **Ajouter**

### 10.4 Créer une Application

1. Dans l'espace de travail:
   - **Créer une application**
2. Configurer:
   - Nom: `SIGPCB Dashboard`
   - Description
   - Logo (si disponible)
3. Sélectionner les rapports à inclure
4. Définir la navigation
5. **Publier l'application**

---

## 11. MAINTENANCE ET OPTIMISATION

### 11.1 Tâches Quotidiennes

- ✅ Vérifier le succès du rafraîchissement automatique
- ✅ Consulter les logs d'erreur
- ✅ Vérifier les données incohérentes

### 11.2 Tâches Hebdomadaires

- ✅ Analyser les performances des requêtes
- ✅ Vérifier l'utilisation de la capacité
- ✅ Mettre à jour les visuels si nécessaire

### 11.3 Tâches Mensuelles

- ✅ Archiver les anciennes données (> 2 ans)
- ✅ Optimiser le modèle de données
- ✅ Réviser les mesures DAX
- ✅ Former les nouveaux utilisateurs

### 11.4 Optimisation des Performances

#### **Réduire la Taille du Fichier**:
1. Supprimer les colonnes inutilisées
2. Réduire la cardinalité des colonnes texte
3. Utiliser des colonnes calculées au lieu de mesures (si approprié)

#### **Optimiser les Requêtes DAX**:
- Utiliser `CALCULATE` au lieu de `FILTER` quand possible
- Éviter les fonctions `ALL()` sur de grandes tables
- Utiliser des variables (`VAR`) pour éviter les recalculs

#### **Optimiser la Base de Données**:
```sql
-- Vacuum et analyse régulière
VACUUM ANALYZE dossier_sessions;
VACUUM ANALYZE examens;

-- Mettre à jour les statistiques
ANALYZE dossier_sessions;
```

---

## 12. DÉPANNAGE

### 12.1 Problèmes de Connexion

**Symptôme**: Impossible de se connecter à PostgreSQL

**Solutions**:
1. Vérifier les identifiants
2. Vérifier le pare-feu:
   ```bash
   telnet [SERVEUR] 5432
   ```
3. Vérifier le fichier `pg_hba.conf`
4. Contacter l'administrateur système

### 12.2 Échec du Rafraîchissement

**Symptôme**: Le rafraîchissement échoue avec une erreur

**Solutions**:
1. Consulter les logs d'erreur:
   - Power BI Service > Paramètres du jeu de données > Historique d'actualisation
2. Vérifier la passerelle:
   - Gateway online ?
   - Identifiants à jour ?
3. Tester la requête SQL directement sur PostgreSQL
4. Vérifier les permissions de l'utilisateur SQL

### 12.3 Performances Lentes

**Symptôme**: Les visuels prennent du temps à charger

**Solutions**:
1. Activer l'analyseur de performances:
   - **Affichage** > **Analyseur de performances**
2. Identifier les visuels/mesures lents
3. Optimiser les mesures DAX
4. Réduire le nombre de visuels par page
5. Utiliser DirectQuery pour les grandes tables

### 12.4 Données Incohérentes

**Symptôme**: Les chiffres ne correspondent pas

**Solutions**:
1. Vérifier les relations entre tables
2. Vérifier les filtres contextuels
3. Vérifier la direction du filtre croisé
4. Actualiser les données
5. Comparer avec les requêtes SQL brutes

### 12.5 Erreurs DAX

**Erreur Commune**: "A circular dependency was detected"

**Solution**:
- Vérifier les dépendances entre mesures
- Éviter les références circulaires

**Erreur Commune**: "The column 'X' was not found"

**Solution**:
- Vérifier le nom exact de la colonne
- Vérifier que la table est bien chargée

---

## 📞 SUPPORT

Pour toute question ou problème:
- **Email**: support-sigpcb@anatt.bj
- **Documentation**: https://docs.sigpcb.bj
- **Hotline**: +229 XX XX XX XX

---

## 📝 CHANGELOG

| Version | Date | Modifications |
|---------|------|---------------|
| 1.0 | 01/12/2025 | Version initiale |

---

## 📄 LICENCE

© 2025 ANaTT - Tous droits réservés.
Ce document est confidentiel et destiné uniquement à un usage interne.

---

**FIN DU GUIDE**
