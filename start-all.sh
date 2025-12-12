#!/bin/bash

# =============================================================================
# SCRIPT DE DÉMARRAGE COMPLET - SIGPCB
# =============================================================================
# Ce script démarre tous les backends et frontends du projet SIGPCB
# Usage: ./start-all.sh
# =============================================================================

# Couleurs pour les logs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Répertoire racine du projet
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo -e "${BLUE}==================================${NC}"
echo -e "${BLUE}DÉMARRAGE DU SYSTÈME SIGPCB${NC}"
echo -e "${BLUE}==================================${NC}"
echo -e "${YELLOW}Répertoire: ${PROJECT_DIR}${NC}"
echo ""

# Fonction pour vérifier si un port est disponible
check_port() {
    local port=$1
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        return 1 # Port occupé
    else
        return 0 # Port libre
    fi
}

# Fonction pour attendre qu'un port soit disponible
wait_for_port() {
    local port=$1
    local max_wait=30
    local count=0

    while ! nc -z localhost $port 2>/dev/null; do
        if [ $count -ge $max_wait ]; then
            echo -e "${RED}Timeout: Le service sur le port ${port} n'a pas démarré${NC}"
            return 1
        fi
        sleep 1
        count=$((count + 1))
    done
    return 0
}

# Fonction pour démarrer un backend Laravel
start_backend() {
    local name=$1
    local port=$2
    local dir=$3

    echo -e "${GREEN}→ Démarrage de ${name} (Port ${port})...${NC}"

    # Vérifier si le port est disponible
    if ! check_port $port; then
        echo -e "${YELLOW}  Port ${port} déjà utilisé. Arrêt du processus existant...${NC}"
        lsof -ti:$port | xargs kill -9 2>/dev/null
        sleep 2
    fi

    # Vérifier que le répertoire existe
    if [ ! -d "${PROJECT_DIR}/${dir}" ]; then
        echo -e "${RED}  ERREUR: Répertoire ${dir} introuvable${NC}"
        return 1
    fi

    cd "${PROJECT_DIR}/${dir}"

    # Vérifier que le fichier .env existe
    if [ ! -f ".env" ]; then
        echo -e "${YELLOW}  Fichier .env manquant. Copie depuis .env.example...${NC}"
        if [ -f ".env.example" ]; then
            cp .env.example .env
            echo -e "${YELLOW}  N'oubliez pas de configurer le fichier .env !${NC}"
        else
            echo -e "${RED}  ERREUR: .env.example introuvable${NC}"
            return 1
        fi
    fi

    # Démarrer le serveur en arrière-plan
    php artisan serve --host=127.0.0.1 --port=${port} > /tmp/${name}.log 2>&1 &
    local pid=$!
    echo $pid > /tmp/${name}.pid

    # Attendre que le service démarre
    sleep 3

    # Vérifier que le service est bien démarré
    if ps -p $pid > /dev/null; then
        echo -e "${GREEN}  ✓ ${name} démarré avec succès (PID: ${pid})${NC}"
        return 0
    else
        echo -e "${RED}  ✗ Échec du démarrage de ${name}${NC}"
        echo -e "${RED}  Consultez les logs: tail -f /tmp/${name}.log${NC}"
        return 1
    fi
}

# Fonction pour démarrer un frontend Angular
start_frontend() {
    local name=$1
    local port=$2
    local dir=$3

    echo -e "${GREEN}→ Démarrage de ${name} (Port ${port})...${NC}"

    # Vérifier si le port est disponible
    if ! check_port $port; then
        echo -e "${YELLOW}  Port ${port} déjà utilisé. Arrêt du processus existant...${NC}"
        lsof -ti:$port | xargs kill -9 2>/dev/null
        sleep 2
    fi

    # Vérifier que le répertoire existe
    if [ ! -d "${PROJECT_DIR}/${dir}" ]; then
        echo -e "${RED}  ERREUR: Répertoire ${dir} introuvable${NC}"
        return 1
    fi

    cd "${PROJECT_DIR}/${dir}"

    # Vérifier que node_modules existe
    if [ ! -d "node_modules" ]; then
        echo -e "${YELLOW}  node_modules manquant. Installation...${NC}"
        npm install --legacy-peer-deps
    fi

    # Démarrer le serveur en arrière-plan
    ng serve --host=0.0.0.0 --port=${port} --disable-host-check > /tmp/${name}.log 2>&1 &
    local pid=$!
    echo $pid > /tmp/${name}.pid

    # Attendre que le service démarre (Angular prend plus de temps)
    sleep 5

    # Vérifier que le service est bien démarré
    if ps -p $pid > /dev/null; then
        echo -e "${GREEN}  ✓ ${name} démarré avec succès (PID: ${pid})${NC}"
        return 0
    else
        echo -e "${RED}  ✗ Échec du démarrage de ${name}${NC}"
        echo -e "${RED}  Consultez les logs: tail -f /tmp/${name}.log${NC}"
        return 1
    fi
}

# =============================================================================
# DÉBUT DU DÉMARRAGE
# =============================================================================

# 1. Vérifier PostgreSQL
echo -e "${BLUE}[1/3] Vérification de PostgreSQL...${NC}"
if ! systemctl is-active --quiet postgresql; then
    echo -e "${YELLOW}PostgreSQL n'est pas démarré. Tentative de démarrage...${NC}"
    sudo systemctl start postgresql
    sleep 2
    if systemctl is-active --quiet postgresql; then
        echo -e "${GREEN}✓ PostgreSQL démarré${NC}"
    else
        echo -e "${RED}✗ Impossible de démarrer PostgreSQL${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}✓ PostgreSQL est actif${NC}"
fi
echo ""

# 2. Démarrer les backends
echo -e "${BLUE}[2/3] Démarrage des backends Laravel...${NC}"
start_backend "base-backend" 8000 "sigpcb-base-backend"
start_backend "backoffice-backend" 8001 "sigpcb-backoffice-backend"
start_backend "candidat-backend" 8002 "sigpcb-candidat-backend"
start_backend "autoecole-backend" 8003 "sigpcb-autoecole-backend"
start_backend "metier-backend" 8004 "sigpcb-metier-backend"
echo ""

# Attendre que les backends soient prêts
echo -e "${YELLOW}Attente de la stabilisation des backends...${NC}"
sleep 5
echo ""

# 3. Démarrer les frontends
echo -e "${BLUE}[3/3] Démarrage des frontends Angular...${NC}"
start_frontend "homepage-frontend" 4200 "sigpcb-homepage-frontend"
start_frontend "backoffice-frontend" 4201 "sigpcb-backoffice-frontend"
start_frontend "candidat-frontend" 4202 "sigpcb-candidat-frontend"
start_frontend "autoecole-frontend" 4203 "sigpcb-autoecole-frontend"
start_frontend "metier-frontend" 4204 "sigpcb-metier-frontend"
start_frontend "composition-frontend" 4205 "sigpcb-composition-frontend"
start_frontend "examinateur-frontend" 4206 "sigpcb-examinateur-frontend"
start_frontend "superviseur-frontend" 4207 "sigpcb-superviseur-frontend"
echo ""

# =============================================================================
# RÉSUMÉ
# =============================================================================

echo -e "${GREEN}==================================${NC}"
echo -e "${GREEN}✓ TOUS LES SERVICES SONT DÉMARRÉS${NC}"
echo -e "${GREEN}==================================${NC}"
echo ""

echo -e "${BLUE}URLs d'accès:${NC}"
echo -e "  🏠 Homepage:       ${GREEN}http://localhost:4200${NC}"
echo -e "  👨‍💼 Backoffice:     ${GREEN}http://localhost:4201${NC}"
echo -e "  🎓 Candidat:       ${GREEN}http://localhost:4202${NC}"
echo -e "  🚗 Auto-École:     ${GREEN}http://localhost:4203${NC}"
echo -e "  💼 Métiers:        ${GREEN}http://localhost:4204${NC}"
echo -e "  📝 Composition:    ${GREEN}http://localhost:4205${NC}"
echo -e "  👨‍🏫 Examinateur:    ${GREEN}http://localhost:4206${NC}"
echo -e "  👁️  Superviseur:    ${GREEN}http://localhost:4207${NC}"
echo ""

echo -e "${BLUE}APIs Backend:${NC}"
echo -e "  Base:        ${GREEN}http://localhost:8000${NC}"
echo -e "  Backoffice:  ${GREEN}http://localhost:8001${NC}"
echo -e "  Candidat:    ${GREEN}http://localhost:8002${NC}"
echo -e "  Auto-École:  ${GREEN}http://localhost:8003${NC}"
echo -e "  Métiers:     ${GREEN}http://localhost:8004${NC}"
echo ""

echo -e "${YELLOW}Commandes utiles:${NC}"
echo -e "  • Voir les logs:    ${BLUE}tail -f /tmp/[nom-service].log${NC}"
echo -e "  • Arrêter tout:     ${BLUE}./stop-all.sh${NC}"
echo -e "  • Vérifier statut:  ${BLUE}./check-status.sh${NC}"
echo ""

echo -e "${GREEN}Déploiement terminé ! 🚀${NC}"
