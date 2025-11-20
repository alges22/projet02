<?php

namespace Database\Seeders;

use App\Models\Examen;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->inThePast();
        $this->fromPastToNow();
        $this->fromNowToFuture();
        $this->future();
    }

    /**
     * Crée un examen dont  la date de compo est déjà dans le passé
     *
     */
    private function inThePast()
    {
        $annee = date('Y'); // Récupère l'année en cours
        Carbon::setLocale('fr_FR');


        $now = Carbon::now()->addMonths(-2);
        Examen::create([
            'debut_etude_dossier_at' => $now = $now->addDays(),
            'fin_etude_dossier_at' => $now = $now->copy()->addDays(5),
            'debut_gestion_rejet_at' => $now = $now->copy()->addDays(5),
            'fin_gestion_rejet_at' => $now = $now->copy()->addDays(5),
            'date_convocation' => $now = $now->copy()->addDays(5),
            'date_code' => $now = $now->copy()->addDays(5),
            'date_conduite' => $now = $now->copy()->addDays(5),
            'status' => false,
            'mois' => $now->monthName,
            'annee' => $annee,
            'numero' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     *
     *  Crée un examen dont la date de compo tombe sur aujourd'hui
     */
    private function fromPastToNow()
    {
        $annee = date('Y'); // Récupère l'année en cours
        Carbon::setLocale('fr_FR');


        $now = Carbon::now();
        Examen::create([
            'date_conduite' => $now = $now->copy()->addDays(1),
            'date_code' => $now = $now->copy()->addDays(-1),
            'date_convocation' => $now = $now->copy()->addDays(-2),
            'fin_gestion_rejet_at' => $now = $now->copy()->addDays(-1),
            'debut_gestion_rejet_at' => $now = $now->copy()->addDays(-1),
            'fin_etude_dossier_at' => $now = $now->copy()->addDays(-2),
            'debut_etude_dossier_at' => $now = $now->copy()->addDays(-10),
            'status' => false,
            'mois' => $now->monthName,
            'annee' => $annee,
            'numero' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     *  Crée un examen dont la date de compo est dans le future
     */
    private function fromNowToFuture()
    {
        $annee = date('Y'); // Récupère l'année en cours
        Carbon::setLocale('fr_FR');


        $now = Carbon::now()->addMonths(1);
        Examen::create([
            'debut_etude_dossier_at' => $now = $now->addDays(),
            'fin_etude_dossier_at' => $now = $now->copy()->addDays(5),
            'debut_gestion_rejet_at' => $now = $now->copy()->addDays(5),
            'fin_gestion_rejet_at' => $now = $now->copy()->addDays(5),
            'date_code' => $now = $now->copy()->addDays(5),
            'date_convocation' => $now = $now->copy()->addDays(5),
            'date_conduite' => $now = $now->copy()->addDays(5),
            'status' => false,
            'mois' => $now->monthName,
            'annee' => $annee,
            'numero' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function future()
    {
        $annee = date('Y'); // Récupère l'année en cours
        Carbon::setLocale('fr_FR');


        $now = Carbon::now()->addMonths(2);
        Examen::create([
            'debut_etude_dossier_at' => $now = $now->addDays(),
            'fin_etude_dossier_at' => $now = $now->copy()->addDays(5),
            'debut_gestion_rejet_at' => $now = $now->copy()->addDays(5),
            'fin_gestion_rejet_at' => $now = $now->copy()->addDays(5),
            'date_code' => $now = $now->copy()->addDays(5),
            'date_conduite' => $now = $now->copy()->addDays(5),
            'date_convocation' => $now = $now->copy()->addDays(5),
            'status' => false,
            'mois' => $now->monthName,
            'annee' => $annee,
            'numero' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
