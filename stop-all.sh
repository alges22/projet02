#!/bin/bash

# =============================================================================
# SCRIPT D'ARRÊT COMPLET - SIGPCB
# =============================================================================
# Ce script arrête tous les backends et frontends du projet SIGPCB
# Usage: ./stop-all.sh
# =============================================================================

# Couleurs pour les logs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${RED}==================================${NC}"
echo -e "${RED}ARRÊT DU SYSTÈME SIGPCB${NC}"
echo -e "${RED}==================================${NC}"
echo ""

# Liste des services avec leurs noms et ports
declare -A services
services=(
    ["base-backend"]=8000
    ["backoffice-backend"]=8001
    ["candidat-backend"]=8002
    ["autoecole-backend"]=8003
    ["metier-backend"]=8004
    ["homepage-frontend"]=4200
    ["backoffice-frontend"]=4201
    ["candidat-frontend"]=4202
    ["autoecole-frontend"]=4203
    ["metier-frontend"]=4204
    ["composition-frontend"]=4205
    ["examinateur-frontend"]=4206
    ["superviseur-frontend"]=4207
)

stopped_count=0
failed_count=0

# Arrêter chaque service par PID
echo -e "${BLUE}Arrêt des services par PID...${NC}"
for service in "${!services[@]}"; do
    if [ -f "/tmp/${service}.pid" ]; then
        pid=$(cat /tmp/${service}.pid)
        if ps -p $pid > /dev/null 2>&1; then
            echo -e "${GREEN}→ Arrêt de ${service} (PID: ${pid})...${NC}"
            kill $pid 2>/dev/null
            sleep 1

            # Vérifier si le processus est bien arrêté
            if ! ps -p $pid > /dev/null 2>&1; then
                echo -e "${GREEN}  ✓ ${service} arrêté${NC}"
                rm /tmp/${service}.pid
                ((stopped_count++))
            else
                echo -e "${YELLOW}  ⚠ Force l'arrêt de ${service}...${NC}"
                kill -9 $pid 2>/dev/null
                rm /tmp/${service}.pid
                ((stopped_count++))
            fi
        else
            echo -e "${YELLOW}→ ${service}: Processus ${pid} introuvable${NC}"
            rm /tmp/${service}.pid
        fi
    fi
done
echo ""

# Nettoyage des ports (au cas où des processus seraient encore actifs)
echo -e "${BLUE}Nettoyage des ports...${NC}"
for service in "${!services[@]}"; do
    port=${services[$service]}
    pids=$(lsof -ti :$port 2>/dev/null)

    if [ ! -z "$pids" ]; then
        echo -e "${YELLOW}→ Processus trouvé sur le port ${port}. Arrêt forcé...${NC}"
        for pid in $pids; do
            kill -9 $pid 2>/dev/null
        done
        echo -e "${GREEN}  ✓ Port ${port} libéré${NC}"
    fi
done
echo ""

# Nettoyage des fichiers de log (optionnel)
echo -e "${BLUE}Nettoyage des fichiers temporaires...${NC}"
for service in "${!services[@]}"; do
    if [ -f "/tmp/${service}.log" ]; then
        # Archiver les logs avant de les supprimer
        log_dir="/tmp/sigpcb-logs"
        mkdir -p "$log_dir"
        timestamp=$(date +"%Y%m%d_%H%M%S")
        mv "/tmp/${service}.log" "$log_dir/${service}_${timestamp}.log" 2>/dev/null
    fi
done
echo -e "${GREEN}  ✓ Logs archivés dans /tmp/sigpcb-logs/${NC}"
echo ""

# Résumé
echo -e "${GREEN}==================================${NC}"
echo -e "${GREEN}ARRÊT TERMINÉ${NC}"
echo -e "${GREEN}==================================${NC}"
echo ""

echo -e "${GREEN}Services arrêtés: ${stopped_count}${NC}"

if [ $failed_count -gt 0 ]; then
    echo -e "${RED}Services en échec: ${failed_count}${NC}"
fi

echo ""
echo -e "${BLUE}Vérification finale:${NC}"
active_ports=$(netstat -tuln 2>/dev/null | grep -E ':(8000|8001|8002|8003|8004|4200|4201|4202|4203|4204|4205|4206|4207)' | wc -l)

if [ $active_ports -eq 0 ]; then
    echo -e "${GREEN}  ✓ Tous les ports sont libérés${NC}"
else
    echo -e "${YELLOW}  ⚠ ${active_ports} port(s) encore actif(s)${NC}"
    echo -e "${YELLOW}  Exécutez: netstat -tuln | grep -E ':(8000|8001|8002|8003|8004|4200|4201|4202|4203|4204|4205|4206|4207)'${NC}"
fi

echo ""
echo -e "${GREEN}Système SIGPCB arrêté ! 🛑${NC}"
echo -e "${BLUE}Pour redémarrer: ./start-all.sh${NC}"
