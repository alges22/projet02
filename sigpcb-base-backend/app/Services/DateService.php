<?php

namespace App\Services;

use Exception;

class DateService
{
    /**
     * Liste des mois complets.
     *
     * @var array
     */
    private $fullMonths = [
        'Janvier',
        'Février',
        'Mars',
        'Avril',
        'Mai',
        'Juin',
        'Juillet',
        'Août',
        'Septembre',
        'Octobre',
        'Novembre',
        'Décembre',
    ];

    /**
     * Liste des jours complets.
     *
     * @var array
     */
    private $fullDays = [
        'Lundi',
        'Mardi',
        'Mercredi',
        'Jeudi',
        'Vendredi',
        'Samedi',
        'Dimanche',
    ];

    /**
     * Liste des mois abrégés.
     *
     * @var array
     */
    private $shortMonths = [
        'Jan', // Janvier
        'Fév', // Février
        'Mar', // Mars
        'Avr', // Avril
        'Mai', // Mai
        'Juin', // Juin
        'Juil', // Juillet
        'Août', // Août
        'Sep', // Septembre
        'Oct', // Octobre
        'Nov', // Novembre
        'Déc', // Décembre
    ];

    /**
     * Récupère la liste des mois complets.
     *
     * @return array
     */
    public function getFullMonths(): array
    {
        return $this->fullMonths;
    }

    /**
     * Récupère la liste des jours complets.
     *
     * @return array
     */
    public function getFullDays(): array
    {
        return $this->fullDays;
    }

    /**
     * Récupère la liste des mois complets à partir du mois donné.
     *
     * @param string $start_month Le mois de départ (en format complet ex : 'Janvier').
     * @return array Liste des mois complets à partir du mois donné.
     * @throws Exception Si le mois de départ n'existe pas dans la liste des mois.
     */
    public function getMonthFrom(string $start_month): array
    {
        $startIndex = array_search($start_month, $this->fullMonths);
        if ($startIndex === false) {
            throw new Exception("Le mois de départ n'existe pas dans la liste des mois.");
        }
        return array_slice($this->fullMonths, $startIndex);
    }

    /**
     * Récupère la liste des jours complets à partir du jour donné.
     *
     * @param string $start_day Le jour de départ (en format complet ex : 'Lundi').
     * @return array Liste des jours complets à partir du jour donné.
     * @throws Exception Si le jour de départ n'existe pas dans la liste des jours.
     */
    public function getDayFrom(string $start_day): array
    {
        $startIndex = array_search($start_day, $this->fullDays);
        if ($startIndex === false) {
            throw new Exception("Le jour de départ n'existe pas dans la liste des jours.");
        }

        return array_slice($this->fullDays, $startIndex);
    }

    /**
     * Récupère le mois complet correspondant au mois actuel.
     *
     * @return string Le mois complet actuel (ex : 'Janvier', 'Février', etc.).
     */
    public function getFullMonthNow(): string
    {
        $dayNumber = date('n') - 1;
        return $this->getFullMonthFromIndex($dayNumber);
    }

    /**
     * Récupère le mois complet correspondant au numéro de jour donné.
     *
     * @param int $index Le numéro du jour (1 pour Janvier, 2 pour Février, etc.).
     * @return string Le mois complet correspondant ou 'Janvier' si le numéro est invalide.
     */
    public function getFullMonthFromIndex(int $index): string
    {
        return $this->fullMonths[$index] ?? 'Janvier';
    }

    /**
     * Trouve le mois abrégé correspondant au numéro de mois donné.
     *
     * @param int $month_number Le numéro du mois (0 pour Janvier, 1 pour Février, etc.).
     * @return string Le mois abrégé correspondant (ex : 'Jan' pour Janvier, 'Fév' pour Février, etc.).
     */
    public function findInShortMonth(int $month_number): string
    {
        return $this->shortMonths[$month_number];
    }

    public function hasFullMonth($month)
    {
        return in_array(ucfirst($month), $this->fullMonths);
    }
}
