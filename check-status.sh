#!/bin/bash

# =============================================================================
# SCRIPT DE VÉRIFICATION DE STATUT - SIGPCB
# =============================================================================
# Ce script vérifie le statut de tous les services du projet SIGPCB
# Usage: ./check-status.sh
# =============================================================================

# Couleurs pour les logs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}==================================${NC}"
echo -e "${BLUE}VÉRIFICATION DU STATUT - SIGPCB${NC}"
echo -e "${BLUE}==================================${NC}"
echo ""

# Compteurs
active_count=0
inactive_count=0
total_services=13

# Fonction pour vérifier un service HTTP
check_http_service() {
    local name=$1
    local url=$2
    local type=$3

    # Tenter une connexion HTTP
    if curl -s --connect-timeout 2 "$url" > /dev/null 2>&1; then
        echo -e "${GREEN}  ✓${NC} ${name}"
        echo -e "      ${BLUE}→${NC} $url"
        ((active_count++))
        return 0
    else
        echo -e "${RED}  ✗${NC} ${name}"
        echo -e "      ${RED}→${NC} $url ${RED}(Non accessible)${NC}"
        ((inactive_count++))
        return 1
    fi
}

# Fonction pour vérifier un port
check_port() {
    local port=$1
    if nc -z localhost $port 2>/dev/null; then
        return 0  # Port ouvert
    else
        return 1  # Port fermé
    fi
}

# =============================================================================
# VÉRIFICATION DES BACKENDS
# =============================================================================

echo -e "${BLUE}[1/3] Backends Laravel:${NC}"
echo ""

check_http_service "sigpcb-base-backend" "http://localhost:8000" "backend"
check_http_service "sigpcb-backoffice-backend" "http://localhost:8001" "backend"
check_http_service "sigpcb-candidat-backend" "http://localhost:8002" "backend"
check_http_service "sigpcb-autoecole-backend" "http://localhost:8003" "backend"
check_http_service "sigpcb-metier-backend" "http://localhost:8004" "backend"

echo ""

# =============================================================================
# VÉRIFICATION DES FRONTENDS
# =============================================================================

echo -e "${BLUE}[2/3] Frontends Angular:${NC}"
echo ""

check_http_service "sigpcb-homepage-frontend" "http://localhost:4200" "frontend"
check_http_service "sigpcb-backoffice-frontend" "http://localhost:4201" "frontend"
check_http_service "sigpcb-candidat-frontend" "http://localhost:4202" "frontend"
check_http_service "sigpcb-autoecole-frontend" "http://localhost:4203" "frontend"
check_http_service "sigpcb-metier-frontend" "http://localhost:4204" "frontend"
check_http_service "sigpcb-composition-frontend" "http://localhost:4205" "frontend"
check_http_service "sigpcb-examinateur-frontend" "http://localhost:4206" "frontend"
check_http_service "sigpcb-superviseur-frontend" "http://localhost:4207" "frontend"

echo ""

# =============================================================================
# VÉRIFICATION DE LA BASE DE DONNÉES
# =============================================================================

echo -e "${BLUE}[3/3] Base de données:${NC}"
echo ""

# Vérifier PostgreSQL
if systemctl is-active --quiet postgresql; then
    echo -e "${GREEN}  ✓${NC} PostgreSQL"
    echo -e "      ${BLUE}→${NC} Service actif"

    # Tester la connexion à la base
    if PGPASSWORD=sigpcb_password psql -h localhost -U sigpcb_user -d sigpcb_base -c "SELECT 1;" > /dev/null 2>&1; then
        echo -e "      ${GREEN}→${NC} Connexion à sigpcb_base: OK"
    else
        echo -e "      ${YELLOW}→${NC} Connexion à sigpcb_base: Échec"
    fi
else
    echo -e "${RED}  ✗${NC} PostgreSQL"
    echo -e "      ${RED}→${NC} Service inactif"
fi

echo ""

# =============================================================================
# VÉRIFICATION DES PROCESSUS
# =============================================================================

echo -e "${BLUE}Processus actifs:${NC}"
echo ""

# Compter les processus PHP (backends)
php_count=$(ps aux | grep -c "[p]hp artisan serve")
echo -e "  • Backends Laravel (php artisan serve): ${BLUE}${php_count}${NC}"

# Compter les processus Node (frontends)
node_count=$(ps aux | grep -c "[n]g serve")
echo -e "  • Frontends Angular (ng serve): ${BLUE}${node_count}${NC}"

echo ""

# =============================================================================
# VÉRIFICATION DES PORTS
# =============================================================================

echo -e "${BLUE}État des ports:${NC}"
echo ""

ports=(8000 8001 8002 8003 8004 4200 4201 4202 4203 4204 4205 4206 4207)
open_ports=0

for port in "${ports[@]}"; do
    if check_port $port; then
        ((open_ports++))
    fi
done

echo -e "  • Ports ouverts: ${GREEN}${open_ports}${NC} / ${total_services}"

# Afficher les ports ouverts
echo -e "  • Détails:"
for port in "${ports[@]}"; do
    if check_port $port; then
        echo -e "      ${GREEN}✓${NC} Port ${port}"
    else
        echo -e "      ${RED}✗${NC} Port ${port}"
    fi
done

echo ""

# =============================================================================
# RÉSUMÉ
# =============================================================================

echo -e "${BLUE}==================================${NC}"
echo -e "${BLUE}RÉSUMÉ${NC}"
echo -e "${BLUE}==================================${NC}"
echo ""

echo -e "Services actifs:   ${GREEN}${active_count}${NC} / ${total_services}"
echo -e "Services inactifs: ${RED}${inactive_count}${NC} / ${total_services}"

echo ""

if [ $active_count -eq $total_services ]; then
    echo -e "${GREEN}✓ Tous les services fonctionnent correctement ! 🎉${NC}"
elif [ $active_count -gt 0 ]; then
    echo -e "${YELLOW}⚠ Certains services ne sont pas actifs${NC}"
    echo -e "${YELLOW}  Consultez les logs: ls -lh /tmp/*.log${NC}"
    echo -e "${YELLOW}  Pour redémarrer: ./start-all.sh${NC}"
else
    echo -e "${RED}✗ Aucun service n'est actif${NC}"
    echo -e "${RED}  Pour démarrer: ./start-all.sh${NC}"
fi

echo ""

# =============================================================================
# CONSEILS
# =============================================================================

if [ $inactive_count -gt 0 ]; then
    echo -e "${BLUE}Commandes de dépannage:${NC}"
    echo -e "  • Voir les logs d'un service: ${BLUE}tail -f /tmp/[nom-service].log${NC}"
    echo -e "  • Vérifier les processus PHP: ${BLUE}ps aux | grep php${NC}"
    echo -e "  • Vérifier les processus Node: ${BLUE}ps aux | grep node${NC}"
    echo -e "  • Arrêter tous les services: ${BLUE}./stop-all.sh${NC}"
    echo -e "  • Redémarrer tous les services: ${BLUE}./start-all.sh${NC}"
    echo ""
fi

# =============================================================================
# INFORMATIONS SYSTÈME
# =============================================================================

echo -e "${BLUE}Informations système:${NC}"
echo -e "  • Utilisation CPU:"
top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print "      " 100 - $1"% utilisé"}'

echo -e "  • Utilisation mémoire:"
free -h | awk '/^Mem:/ {print "      " $3 " / " $2 " utilisé"}'

echo -e "  • Espace disque:"
df -h / | awk 'NR==2 {print "      " $3 " / " $2 " utilisé (" $5 ")"}'

echo ""
echo -e "${GREEN}Vérification terminée !${NC}"
