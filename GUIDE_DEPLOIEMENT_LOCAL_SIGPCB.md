# 🚀 GUIDE DE DÉPLOIEMENT LOCAL - SIGPCB

## Système Intégré de Gestion du Permis de Conduire au Bénin

**Version:** 1.0
**Date:** 01 Décembre 2025
**Environnement:** Développement Local

---

## 📋 TABLE DES MATIÈRES

1. [Prérequis](#1-prérequis)
2. [Architecture du Projet](#2-architecture-du-projet)
3. [Configuration de la Base de Données](#3-configuration-de-la-base-de-données)
4. [Déploiement des Backends Laravel](#4-déploiement-des-backends-laravel)
5. [Déploiement des Frontends Angular](#5-déploiement-des-frontends-angular)
6. [Configuration des URLs et Ports](#6-configuration-des-urls-et-ports)
7. [Démarrage Complet du Système](#7-démarrage-complet-du-système)
8. [Tests et Validation](#8-tests-et-validation)
9. [Dépannage](#9-dépannage)
10. [Scripts Automatisés](#10-scripts-automatisés)

---

## 1. PRÉREQUIS

### 1.1 Logiciels Requis

#### **PHP et Composer**
```bash
# Vérifier PHP (version 8.0+)
php -v

# Vérifier Composer
composer --version

# Si non installé:
# Ubuntu/Debian
sudo apt update
sudo apt install php8.1 php8.1-cli php8.1-fpm php8.1-pgsql php8.1-mbstring php8.1-xml php8.1-curl
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### **Node.js et NPM**
```bash
# Vérifier Node.js (version 16+ ou 18+)
node -v

# Vérifier NPM
npm -v

# Si non installé:
# Ubuntu/Debian
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

#### **PostgreSQL**
```bash
# Vérifier PostgreSQL (version 13+)
psql --version

# Si non installé:
# Ubuntu/Debian
sudo apt install postgresql postgresql-contrib

# Démarrer PostgreSQL
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

#### **Git**
```bash
# Vérifier Git
git --version

# Si non installé:
sudo apt install git
```

### 1.2 Configuration Système Minimale

- **RAM**: 8 GB minimum (16 GB recommandé)
- **Espace Disque**: 10 GB minimum
- **OS**: Linux (Ubuntu 20.04+), macOS, ou Windows avec WSL2

---

## 2. ARCHITECTURE DU PROJET

### 2.1 Structure des Modules

Le projet SIGPCB est composé de **16 modules** :

#### **Backends Laravel (5 modules)**
1. `sigpcb-base-backend` - Services de base (Port: 8000)
2. `sigpcb-backoffice-backend` - Administration (Port: 8001)
3. `sigpcb-candidat-backend` - Services candidats (Port: 8002)
4. `sigpcb-autoecole-backend` - Services auto-écoles (Port: 8003)
5. `sigpcb-metier-backend` - Services métiers (Port: 8004)

#### **Frontends Angular (7 modules)**
1. `sigpcb-homepage-frontend` - Page d'accueil (Port: 4200)
2. `sigpcb-backoffice-frontend` - Admin frontend (Port: 4201)
3. `sigpcb-candidat-frontend` - Candidat frontend (Port: 4202)
4. `sigpcb-autoecole-frontend` - Auto-école frontend (Port: 4203)
5. `sigpcb-metier-frontend` - Métiers frontend (Port: 4204)
6. `sigpcb-composition-frontend` - Composition examen (Port: 4205)
7. `sigpcb-examinateur-frontend` - Examinateur frontend (Port: 4206)
8. `sigpcb-superviseur-frontend` - Superviseur frontend (Port: 4207)

#### **Autres**
- `sigpcb-composition-backend` - (Vide, non utilisé)

### 2.2 Bases de Données

- **Base principale**: `sigpcb_base` (PostgreSQL)
- **Schéma**: `public`
- **Tables principales**: ~50 tables

---

## 3. CONFIGURATION DE LA BASE DE DONNÉES

### 3.1 Création de la Base de Données

```bash
# Se connecter à PostgreSQL
sudo -u postgres psql

# Dans PostgreSQL, exécuter:
```

```sql
-- Créer l'utilisateur
CREATE USER sigpcb_user WITH PASSWORD 'sigpcb_password';

-- Créer la base de données
CREATE DATABASE sigpcb_base OWNER sigpcb_user;

-- Accorder les privilèges
GRANT ALL PRIVILEGES ON DATABASE sigpcb_base TO sigpcb_user;

-- Se connecter à la base
\c sigpcb_base

-- Accorder les privilèges sur le schéma public
GRANT ALL ON SCHEMA public TO sigpcb_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO sigpcb_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO sigpcb_user;

-- Quitter
\q
```

### 3.2 Vérification de la Connexion

```bash
# Tester la connexion
psql -h localhost -U sigpcb_user -d sigpcb_base

# Si connexion réussie, quitter
\q
```

### 3.3 Configuration pg_hba.conf (si erreurs d'authentification)

```bash
# Éditer le fichier pg_hba.conf
sudo nano /etc/postgresql/13/main/pg_hba.conf

# Ajouter cette ligne (avant les autres):
# local   all             sigpcb_user                             md5
# host    all             sigpcb_user     127.0.0.1/32            md5

# Redémarrer PostgreSQL
sudo systemctl restart postgresql
```

---

## 4. DÉPLOIEMENT DES BACKENDS LARAVEL

### 4.1 Configuration Commune pour Tous les Backends

#### **Variables d'environnement communes**

Chaque backend nécessite un fichier `.env`. Voici les étapes pour chaque:

```bash
# Se positionner dans le répertoire du projet
cd /home/user/projet02
```

### 4.2 BACKEND 1: sigpcb-base-backend

```bash
# 1. Aller dans le répertoire
cd sigpcb-base-backend

# 2. Copier le fichier .env.example
cp .env.example .env

# 3. Éditer le .env
nano .env
```

**Contenu du fichier `.env` pour sigpcb-base-backend:**
```env
APP_NAME="SIGPCB Base Backend"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Base de données PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sigpcb_base
DB_USERNAME=sigpcb_user
DB_PASSWORD=sigpcb_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Configuration CORS
CORS_ALLOWED_ORIGINS="http://localhost:4200,http://localhost:4201,http://localhost:4202,http://localhost:4203,http://localhost:4204,http://localhost:4205,http://localhost:4206,http://localhost:4207"

# Tokens ANaTT (si applicable)
ANATT_ATK_PUBLIC_COMPO=B8_20315emekdjsaaejlmd69877
ANATT_ATK_PRIVATE_COMPO=ana8_20315eme45aaejlmd6945
```

```bash
# 4. Installer les dépendances PHP
composer install

# 5. Générer la clé d'application
php artisan key:generate

# 6. Créer le lien symbolique pour storage
php artisan storage:link

# 7. Exécuter les migrations
php artisan migrate

# Si vous avez des seeders pour les données de test:
# php artisan db:seed

# 8. Démarrer le serveur (Port 8000)
php artisan serve --host=127.0.0.1 --port=8000
```

**Le backend devrait être accessible à**: `http://localhost:8000`

---

### 4.3 BACKEND 2: sigpcb-backoffice-backend

**Ouvrir un nouveau terminal** et exécuter:

```bash
# 1. Aller dans le répertoire
cd /home/user/projet02/sigpcb-backoffice-backend

# 2. Copier le fichier .env
cp .env.example .env

# 3. Éditer le .env
nano .env
```

**Contenu du fichier `.env` pour sigpcb-backoffice-backend:**
```env
APP_NAME="SIGPCB Backoffice Backend"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8001

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Base de données PostgreSQL (même base que base-backend)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sigpcb_base
DB_USERNAME=sigpcb_user
DB_PASSWORD=sigpcb_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# URL du backend de base
BASE_BACKEND_URL=http://localhost:8000

# Configuration CORS
CORS_ALLOWED_ORIGINS="http://localhost:4200,http://localhost:4201"
```

```bash
# 4. Installer les dépendances PHP
composer install

# 5. Générer la clé d'application
php artisan key:generate

# 6. Créer le lien symbolique pour storage
php artisan storage:link

# 7. Démarrer le serveur (Port 8001)
php artisan serve --host=127.0.0.1 --port=8001
```

**Le backend devrait être accessible à**: `http://localhost:8001`

---

### 4.4 BACKEND 3: sigpcb-candidat-backend

**Ouvrir un nouveau terminal** et exécuter:

```bash
# 1. Aller dans le répertoire
cd /home/user/projet02/sigpcb-candidat-backend

# 2. Copier le fichier .env
cp .env.example .env

# 3. Éditer le .env
nano .env
```

**Contenu du fichier `.env` pour sigpcb-candidat-backend:**
```env
APP_NAME="SIGPCB Candidat Backend"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8002

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Base de données PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sigpcb_base
DB_USERNAME=sigpcb_user
DB_PASSWORD=sigpcb_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# URL du backend de base
BASE_BACKEND_URL=http://localhost:8000

# Configuration CORS
CORS_ALLOWED_ORIGINS="http://localhost:4202"
```

```bash
# 4. Installer les dépendances PHP
composer install

# 5. Générer la clé d'application
php artisan key:generate

# 6. Créer le lien symbolique pour storage
php artisan storage:link

# 7. Démarrer le serveur (Port 8002)
php artisan serve --host=127.0.0.1 --port=8002
```

**Le backend devrait être accessible à**: `http://localhost:8002`

---

### 4.5 BACKEND 4: sigpcb-autoecole-backend

**Ouvrir un nouveau terminal** et exécuter:

```bash
# 1. Aller dans le répertoire
cd /home/user/projet02/sigpcb-autoecole-backend

# 2. Copier le fichier .env
cp .env.example .env

# 3. Éditer le .env
nano .env
```

**Contenu du fichier `.env` pour sigpcb-autoecole-backend:**
```env
APP_NAME="SIGPCB AutoEcole Backend"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8003

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Base de données PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sigpcb_base
DB_USERNAME=sigpcb_user
DB_PASSWORD=sigpcb_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# URL du backend de base
BASE_BACKEND_URL=http://localhost:8000

# Configuration CORS
CORS_ALLOWED_ORIGINS="http://localhost:4203"
```

```bash
# 4. Installer les dépendances PHP
composer install

# 5. Générer la clé d'application
php artisan key:generate

# 6. Créer le lien symbolique pour storage
php artisan storage:link

# 7. Démarrer le serveur (Port 8003)
php artisan serve --host=127.0.0.1 --port=8003
```

**Le backend devrait être accessible à**: `http://localhost:8003`

---

### 4.6 BACKEND 5: sigpcb-metier-backend

**Ouvrir un nouveau terminal** et exécuter:

```bash
# 1. Aller dans le répertoire
cd /home/user/projet02/sigpcb-metier-backend

# 2. Copier le fichier .env
cp .env.example .env

# 3. Éditer le .env
nano .env
```

**Contenu du fichier `.env` pour sigpcb-metier-backend:**
```env
APP_NAME="SIGPCB Metier Backend"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8004

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Base de données PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sigpcb_base
DB_USERNAME=sigpcb_user
DB_PASSWORD=sigpcb_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# URL du backend de base
BASE_BACKEND_URL=http://localhost:8000

# Configuration CORS
CORS_ALLOWED_ORIGINS="http://localhost:4204"
```

```bash
# 4. Installer les dépendances PHP
composer install

# 5. Générer la clé d'application
php artisan key:generate

# 6. Créer le lien symbolique pour storage
php artisan storage:link

# 7. Démarrer le serveur (Port 8004)
php artisan serve --host=127.0.0.1 --port=8004
```

**Le backend devrait être accessible à**: `http://localhost:8004`

---

## 5. DÉPLOIEMENT DES FRONTENDS ANGULAR

### 5.1 Configuration Commune pour Tous les Frontends

Chaque frontend Angular nécessite la configuration de l'URL du backend correspondant.

### 5.2 FRONTEND 1: sigpcb-homepage-frontend

**Ouvrir un nouveau terminal** et exécuter:

```bash
# 1. Aller dans le répertoire
cd /home/user/projet02/sigpcb-homepage-frontend

# 2. Installer les dépendances Node.js
npm install

# 3. Configurer l'environnement (si fichier environment existe)
# Éditer src/environments/environment.ts
nano src/environments/environment.ts
```

**Contenu suggéré pour `environment.ts`:**
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000'
};
```

```bash
# 4. Démarrer le serveur de développement (Port 4200)
ng serve --host=0.0.0.0 --port=4200
```

**Le frontend devrait être accessible à**: `http://localhost:4200`

---

### 5.3 FRONTEND 2: sigpcb-backoffice-frontend

**Ouvrir un nouveau terminal** et exécuter:

```bash
# 1. Aller dans le répertoire
cd /home/user/projet02/sigpcb-backoffice-frontend

# 2. Installer les dépendances Node.js
npm install

# 3. Configurer l'environnement
nano src/environments/environment.ts
```

**Contenu pour `environment.ts`:**
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8001',
  baseApiUrl: 'http://localhost:8000'
};
```

```bash
# 4. Démarrer le serveur de développement (Port 4201)
ng serve --host=0.0.0.0 --port=4201
```

**Le frontend devrait être accessible à**: `http://localhost:4201`

---

### 5.4 FRONTEND 3: sigpcb-candidat-frontend

**Ouvrir un nouveau terminal** et exécuter:

```bash
# 1. Aller dans le répertoire
cd /home/user/projet02/sigpcb-candidat-frontend

# 2. Installer les dépendances Node.js
npm install

# 3. Configurer l'environnement
nano src/environments/environment.ts
```

**Contenu pour `environment.ts`:**
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8002',
  baseApiUrl: 'http://localhost:8000'
};
```

```bash
# 4. Démarrer le serveur de développement (Port 4202)
ng serve --host=0.0.0.0 --port=4202
```

**Le frontend devrait être accessible à**: `http://localhost:4202`

---

### 5.5 FRONTEND 4: sigpcb-autoecole-frontend

**Ouvrir un nouveau terminal** et exécuter:

```bash
# 1. Aller dans le répertoire
cd /home/user/projet02/sigpcb-autoecole-frontend

# 2. Installer les dépendances Node.js
npm install

# 3. Configurer l'environnement
nano src/environments/environment.ts
```

**Contenu pour `environment.ts`:**
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8003',
  baseApiUrl: 'http://localhost:8000'
};
```

```bash
# 4. Démarrer le serveur de développement (Port 4203)
ng serve --host=0.0.0.0 --port=4203
```

**Le frontend devrait être accessible à**: `http://localhost:4203`

---

### 5.6 FRONTEND 5: sigpcb-metier-frontend

**Ouvrir un nouveau terminal** et exécuter:

```bash
# 1. Aller dans le répertoire
cd /home/user/projet02/sigpcb-metier-frontend

# 2. Installer les dépendances Node.js
npm install

# 3. Configurer l'environnement
nano src/environments/environment.ts
```

**Contenu pour `environment.ts`:**
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8004',
  baseApiUrl: 'http://localhost:8000'
};
```

```bash
# 4. Démarrer le serveur de développement (Port 4204)
ng serve --host=0.0.0.0 --port=4204
```

**Le frontend devrait être accessible à**: `http://localhost:4204`

---

### 5.7 FRONTEND 6: sigpcb-composition-frontend

**Ouvrir un nouveau terminal** et exécuter:

```bash
# 1. Aller dans le répertoire
cd /home/user/projet02/sigpcb-composition-frontend

# 2. Installer les dépendances Node.js
npm install

# 3. Configurer l'environnement
nano src/environments/environment.ts
```

**Contenu pour `environment.ts`:**
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000', // Utilise le backend de base
  baseApiUrl: 'http://localhost:8000'
};
```

```bash
# 4. Démarrer le serveur de développement (Port 4205)
ng serve --host=0.0.0.0 --port=4205
```

**Le frontend devrait être accessible à**: `http://localhost:4205`

---

### 5.8 FRONTEND 7: sigpcb-examinateur-frontend

**Ouvrir un nouveau terminal** et exécuter:

```bash
# 1. Aller dans le répertoire
cd /home/user/projet02/sigpcb-examinateur-frontend

# 2. Installer les dépendances Node.js
npm install

# 3. Configurer l'environnement
nano src/environments/environment.ts
```

**Contenu pour `environment.ts`:**
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8004',
  baseApiUrl: 'http://localhost:8000'
};
```

```bash
# 4. Démarrer le serveur de développement (Port 4206)
ng serve --host=0.0.0.0 --port=4206
```

**Le frontend devrait être accessible à**: `http://localhost:4206`

---

### 5.9 FRONTEND 8: sigpcb-superviseur-frontend

**Ouvrir un nouveau terminal** et exécuter:

```bash
# 1. Aller dans le répertoire
cd /home/user/projet02/sigpcb-superviseur-frontend

# 2. Installer les dépendances Node.js
npm install

# 3. Configurer l'environnement
nano src/environments/environment.ts
```

**Contenu pour `environment.ts`:**
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8004',
  baseApiUrl: 'http://localhost:8000'
};
```

```bash
# 4. Démarrer le serveur de développement (Port 4207)
ng serve --host=0.0.0.0 --port=4207
```

**Le frontend devrait être accessible à**: `http://localhost:4207`

---

## 6. CONFIGURATION DES URLS ET PORTS

### 6.1 Tableau Récapitulatif

| Module | Type | Port | URL |
|--------|------|------|-----|
| **sigpcb-base-backend** | Backend | 8000 | http://localhost:8000 |
| **sigpcb-backoffice-backend** | Backend | 8001 | http://localhost:8001 |
| **sigpcb-candidat-backend** | Backend | 8002 | http://localhost:8002 |
| **sigpcb-autoecole-backend** | Backend | 8003 | http://localhost:8003 |
| **sigpcb-metier-backend** | Backend | 8004 | http://localhost:8004 |
| **sigpcb-homepage-frontend** | Frontend | 4200 | http://localhost:4200 |
| **sigpcb-backoffice-frontend** | Frontend | 4201 | http://localhost:4201 |
| **sigpcb-candidat-frontend** | Frontend | 4202 | http://localhost:4202 |
| **sigpcb-autoecole-frontend** | Frontend | 4203 | http://localhost:4203 |
| **sigpcb-metier-frontend** | Frontend | 4204 | http://localhost:4204 |
| **sigpcb-composition-frontend** | Frontend | 4205 | http://localhost:4205 |
| **sigpcb-examinateur-frontend** | Frontend | 4206 | http://localhost:4206 |
| **sigpcb-superviseur-frontend** | Frontend | 4207 | http://localhost:4207 |

### 6.2 Vérifier que les Ports sont Disponibles

```bash
# Vérifier quels ports sont utilisés
sudo netstat -tuln | grep -E ':(8000|8001|8002|8003|8004|4200|4201|4202|4203|4204|4205|4206|4207)'

# Si un port est occupé, tuer le processus:
# Trouver le PID
sudo lsof -i :8000

# Tuer le processus
sudo kill -9 [PID]
```

---

## 7. DÉMARRAGE COMPLET DU SYSTÈME

### 7.1 Ordre de Démarrage Recommandé

1. **Base de données PostgreSQL** (doit être démarrée en premier)
2. **sigpcb-base-backend** (backend principal)
3. **Autres backends** (dans n'importe quel ordre)
4. **Frontends** (dans n'importe quel ordre)

### 7.2 Script de Démarrage Complet

Créer un fichier `start-all.sh`:

```bash
#!/bin/bash

# Couleurs pour les logs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}==================================${NC}"
echo -e "${BLUE}DÉMARRAGE DU SYSTÈME SIGPCB${NC}"
echo -e "${BLUE}==================================${NC}"

# Répertoire racine du projet
PROJECT_DIR="/home/user/projet02"

# Fonction pour démarrer un backend Laravel
start_backend() {
    local name=$1
    local port=$2
    local dir=$3

    echo -e "${GREEN}Démarrage de ${name} (Port ${port})...${NC}"
    cd "${PROJECT_DIR}/${dir}"
    php artisan serve --host=127.0.0.1 --port=${port} > /tmp/${name}.log 2>&1 &
    echo $! > /tmp/${name}.pid
    sleep 2
}

# Fonction pour démarrer un frontend Angular
start_frontend() {
    local name=$1
    local port=$2
    local dir=$3

    echo -e "${GREEN}Démarrage de ${name} (Port ${port})...${NC}"
    cd "${PROJECT_DIR}/${dir}"
    ng serve --host=0.0.0.0 --port=${port} > /tmp/${name}.log 2>&1 &
    echo $! > /tmp/${name}.pid
    sleep 3
}

# Vérifier PostgreSQL
echo -e "${BLUE}Vérification de PostgreSQL...${NC}"
sudo systemctl status postgresql > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo -e "${RED}PostgreSQL n'est pas démarré. Démarrage...${NC}"
    sudo systemctl start postgresql
fi

# Démarrer les backends
start_backend "base-backend" 8000 "sigpcb-base-backend"
start_backend "backoffice-backend" 8001 "sigpcb-backoffice-backend"
start_backend "candidat-backend" 8002 "sigpcb-candidat-backend"
start_backend "autoecole-backend" 8003 "sigpcb-autoecole-backend"
start_backend "metier-backend" 8004 "sigpcb-metier-backend"

# Attendre que les backends soient prêts
echo -e "${BLUE}Attente du démarrage des backends...${NC}"
sleep 5

# Démarrer les frontends
start_frontend "homepage-frontend" 4200 "sigpcb-homepage-frontend"
start_frontend "backoffice-frontend" 4201 "sigpcb-backoffice-frontend"
start_frontend "candidat-frontend" 4202 "sigpcb-candidat-frontend"
start_frontend "autoecole-frontend" 4203 "sigpcb-autoecole-frontend"
start_frontend "metier-frontend" 4204 "sigpcb-metier-frontend"
start_frontend "composition-frontend" 4205 "sigpcb-composition-frontend"
start_frontend "examinateur-frontend" 4206 "sigpcb-examinateur-frontend"
start_frontend "superviseur-frontend" 4207 "sigpcb-superviseur-frontend"

echo -e "${GREEN}==================================${NC}"
echo -e "${GREEN}TOUS LES SERVICES SONT DÉMARRÉS${NC}"
echo -e "${GREEN}==================================${NC}"

echo ""
echo -e "${BLUE}URLs d'accès:${NC}"
echo -e "Homepage:       http://localhost:4200"
echo -e "Backoffice:     http://localhost:4201"
echo -e "Candidat:       http://localhost:4202"
echo -e "Auto-École:     http://localhost:4203"
echo -e "Métiers:        http://localhost:4204"
echo -e "Composition:    http://localhost:4205"
echo -e "Examinateur:    http://localhost:4206"
echo -e "Superviseur:    http://localhost:4207"
echo ""
echo -e "${BLUE}Logs disponibles dans /tmp/[nom-service].log${NC}"
echo -e "${BLUE}Pour arrêter tous les services: ./stop-all.sh${NC}"
```

**Rendre le script exécutable:**
```bash
chmod +x start-all.sh
```

**Exécuter le script:**
```bash
./start-all.sh
```

---

### 7.3 Script d'Arrêt Complet

Créer un fichier `stop-all.sh`:

```bash
#!/bin/bash

# Couleurs pour les logs
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${RED}==================================${NC}"
echo -e "${RED}ARRÊT DU SYSTÈME SIGPCB${NC}"
echo -e "${RED}==================================${NC}"

# Liste des services
services=(
    "base-backend"
    "backoffice-backend"
    "candidat-backend"
    "autoecole-backend"
    "metier-backend"
    "homepage-frontend"
    "backoffice-frontend"
    "candidat-frontend"
    "autoecole-frontend"
    "metier-frontend"
    "composition-frontend"
    "examinateur-frontend"
    "superviseur-frontend"
)

# Arrêter chaque service
for service in "${services[@]}"; do
    if [ -f "/tmp/${service}.pid" ]; then
        echo -e "${GREEN}Arrêt de ${service}...${NC}"
        kill $(cat /tmp/${service}.pid) 2>/dev/null
        rm /tmp/${service}.pid
    fi
done

# Tuer tous les processus restants sur les ports
echo -e "${GREEN}Nettoyage des ports...${NC}"
for port in 8000 8001 8002 8003 8004 4200 4201 4202 4203 4204 4205 4206 4207; do
    pid=$(lsof -t -i:$port 2>/dev/null)
    if [ ! -z "$pid" ]; then
        echo -e "${GREEN}Arrêt du processus sur le port ${port}...${NC}"
        kill -9 $pid 2>/dev/null
    fi
done

echo -e "${GREEN}==================================${NC}"
echo -e "${GREEN}TOUS LES SERVICES SONT ARRÊTÉS${NC}"
echo -e "${GREEN}==================================${NC}"
```

**Rendre le script exécutable:**
```bash
chmod +x stop-all.sh
```

**Exécuter le script:**
```bash
./stop-all.sh
```

---

## 8. TESTS ET VALIDATION

### 8.1 Vérifier que Tous les Services Fonctionnent

```bash
# Script de vérification
#!/bin/bash

echo "Vérification des services..."

# Backends
curl -s http://localhost:8000 > /dev/null && echo "✓ Base Backend OK" || echo "✗ Base Backend ERREUR"
curl -s http://localhost:8001 > /dev/null && echo "✓ Backoffice Backend OK" || echo "✗ Backoffice Backend ERREUR"
curl -s http://localhost:8002 > /dev/null && echo "✓ Candidat Backend OK" || echo "✗ Candidat Backend ERREUR"
curl -s http://localhost:8003 > /dev/null && echo "✓ AutoEcole Backend OK" || echo "✗ AutoEcole Backend ERREUR"
curl -s http://localhost:8004 > /dev/null && echo "✓ Metier Backend OK" || echo "✗ Metier Backend ERREUR"

# Frontends
curl -s http://localhost:4200 > /dev/null && echo "✓ Homepage Frontend OK" || echo "✗ Homepage Frontend ERREUR"
curl -s http://localhost:4201 > /dev/null && echo "✓ Backoffice Frontend OK" || echo "✗ Backoffice Frontend ERREUR"
curl -s http://localhost:4202 > /dev/null && echo "✓ Candidat Frontend OK" || echo "✗ Candidat Frontend ERREUR"
curl -s http://localhost:4203 > /dev/null && echo "✓ AutoEcole Frontend OK" || echo "✗ AutoEcole Frontend ERREUR"
curl -s http://localhost:4204 > /dev/null && echo "✓ Metier Frontend OK" || echo "✗ Metier Frontend ERREUR"
curl -s http://localhost:4205 > /dev/null && echo "✓ Composition Frontend OK" || echo "✗ Composition Frontend ERREUR"
curl -s http://localhost:4206 > /dev/null && echo "✓ Examinateur Frontend OK" || echo "✗ Examinateur Frontend ERREUR"
curl -s http://localhost:4207 > /dev/null && echo "✓ Superviseur Frontend OK" || echo "✗ Superviseur Frontend ERREUR"
```

### 8.2 Consulter les Logs

```bash
# Logs des backends
tail -f /tmp/base-backend.log
tail -f /tmp/backoffice-backend.log

# Logs des frontends
tail -f /tmp/homepage-frontend.log
tail -f /tmp/backoffice-frontend.log

# Logs Laravel (si configurés)
tail -f sigpcb-base-backend/storage/logs/laravel.log
```

---

## 9. DÉPANNAGE

### 9.1 Problème: "Port déjà utilisé"

```bash
# Trouver quel processus utilise le port
sudo lsof -i :8000

# Tuer le processus
sudo kill -9 [PID]
```

### 9.2 Problème: "Composer dependencies not installed"

```bash
# Aller dans le répertoire du backend
cd sigpcb-base-backend

# Réinstaller les dépendances
rm -rf vendor
composer install
```

### 9.3 Problème: "npm ERR! peer dependencies"

```bash
# Aller dans le répertoire du frontend
cd sigpcb-backoffice-frontend

# Réinstaller avec --legacy-peer-deps
rm -rf node_modules
npm install --legacy-peer-deps
```

### 9.4 Problème: "SQLSTATE[08006] Connection refused"

```bash
# Vérifier que PostgreSQL est démarré
sudo systemctl status postgresql

# Démarrer PostgreSQL si nécessaire
sudo systemctl start postgresql

# Vérifier la connexion
psql -h localhost -U sigpcb_user -d sigpcb_base
```

### 9.5 Problème: "APP_KEY not set"

```bash
# Générer une nouvelle clé
php artisan key:generate
```

### 9.6 Problème: "CORS errors"

Vérifier que le fichier `.env` du backend contient:
```env
CORS_ALLOWED_ORIGINS="http://localhost:4200,http://localhost:4201,..."
```

---

## 10. SCRIPTS AUTOMATISÉS

Tous les scripts sont disponibles dans le répertoire racine du projet.

### 10.1 Scripts Disponibles

```bash
# Démarrer tous les services
./start-all.sh

# Arrêter tous les services
./stop-all.sh

# Vérifier le statut
./check-status.sh

# Réinstaller toutes les dépendances
./reinstall-all.sh
```

---

## ✅ CHECKLIST DE DÉPLOIEMENT

- [ ] PostgreSQL installé et démarré
- [ ] PHP 8.0+ installé
- [ ] Composer installé
- [ ] Node.js 16+ installé
- [ ] Base de données `sigpcb_base` créée
- [ ] Utilisateur PostgreSQL `sigpcb_user` créé
- [ ] Tous les backends configurés (.env)
- [ ] Tous les frontends configurés (environment.ts)
- [ ] Dépendances PHP installées (composer install)
- [ ] Dépendances Node installées (npm install)
- [ ] Migrations exécutées (php artisan migrate)
- [ ] Tous les services démarrés
- [ ] URLs testées et fonctionnelles

---

## 📞 SUPPORT

Pour toute question ou problème:
- **Email**: support-sigpcb@anatt.bj
- **Documentation**: https://docs.sigpcb.bj

---

**Bonne chance avec votre déploiement ! 🚀**
