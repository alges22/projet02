<?php

namespace App\Services\Imports;

use App\Services\Help;
use App\Models\AutoEcole\Licence;
use App\Models\AutoEcole\Agrement;
use App\Models\AutoEcole\Moniteur;
use App\Models\AutoEcole\Vehicule;
use App\Models\AutoEcole\AutoEcole;
use App\Models\AutoEcole\Promoteur;
use App\Models\AutoEcole\OldAutoEcole;
use App\Services\Imports\FromExcelRow;
use App\Models\AutoEcole\DemandeLicence;
use App\Models\AutoEcole\DemandeAgrement;

class ExcelImportation
{
    public function __construct(private FromExcelRow $row, private array $validated)
    {
    }

    /**
     * Faire tout le code d'importation ici
     *
     * @return $this
     */
    public function create()
    {
        /**
         *
         *
         * ----------------------------------------------------------------
         * Ce code est le point d'entrée pour importer toutes les données
         *
         * ----------------------------------------------------------------
         *
         *
         */


        # Récupération par exemple de la dénomination
        try {

                $commune = $this->validated['commune'];
                $departement = $this->validated['departement'];
    
                $fillable = [
                    "name" => $this->validated['name'],
                    'departement' => $departement->name,
                    'commune' => $commune->name,
                    'moniteur_npis' => $this->validated['moniteurs_npis'],
                    'promoteur_npi' => $this->validated['npi'],
                    'adresse' => $this->validated['adresse'],
                    'agrement' => $this->validated['agrement'],
                    'expire_licence' => $this->validated['data_licence'],
                    'code_licence' => $this->validated['licence'],
                    'ifu' => $this->validated['ifu'],
                    'email_pro' => $this->validated['email'],
                    'email_promoteur' => $this->validated['emailPromoteur'],
                    'telephone_pro' => $this->validated['phone'],
                    'vehicules' => $this->validated['immatriculations'],
                ];
                $ae = OldAutoEcole::updateOrCreate([
                    'name' => $this->validated['name'],
                ], $fillable);
    
                $moniteurs =  $this->validated['moniteurs'];
                $vehicules =  $this->validated['vehicules'];
    
                $promoteur =  Promoteur::updateOrCreate([
                    "npi" => $this->validated['npi'],
    
                ], [
                    'npi' => $this->validated['npi'],
                    'email' => $this->validated['emailPromoteur'],
                ]);


            // dd($promoteur->id);
            $demandeAgrement = DemandeAgrement::updateOrCreate([
                'auto_ecole' => $ae->name,
                'ifu' => $ae->ifu,
            ], [
                'auto_ecole' => $ae->name,
                'promoteur_npi' => $ae->promoteur_npi,
                'departement_id' => $departement->id,
                'commune_id' => $commune->id,
                'moniteurs' => json_encode($moniteurs),
                'telephone_pro' => $ae->telephone_pro,
                'email_pro' => $ae->email_pro,
                'email_promoteur' => $ae->email_promoteur,
                'promoteur_id' => $promoteur->id,
                'state' => "validate",
            ]);

            Help::historique(
                'agrement',
                'Agrément importée',
                'demande-agrement-validate',
                $message = "L'ANaTT a importé avec succèes votre agrément de votre auto-école {$ae->name}.",
                $promoteur,
                $demandeAgrement
            );
            $dateObtentionAgrement = now();
            $agrement = Agrement::updateOrCreate([
                'demande_agrement_id' => $demandeAgrement->id,
            ], [
                'promoteur_id' => $promoteur->id,
                'date_obtention' => $dateObtentionAgrement,
                'code' => $ae->agrement,
                'demande_agrement_id' => $demandeAgrement->id,
            ]);
            // Vérifier l'unicité de l'email

            $autoEcole = AutoEcole::updateOrCreate([
                'agrement_id' => $agrement->id,
            ], [
                'agrement_id' => $agrement->id,
                'name' => $ae->name,
                'email' => $ae->email_pro,
                'phone' => $ae->telephone_pro,
                'code' => rand(100000, 999999),
                'num_ifu' => $ae->ifu,
                'promoteur_id' => $promoteur->id,
                'departement_id' => $departement->id,
                'commune_id' => $commune->id,
                'adresse' => $ae->adresse,
                'imported' => true,
                "slug" => $this->validated['slug']
            ]);

            foreach ($moniteurs as $key => $npi) {
                Moniteur::updateOrCreate([
                    'auto_ecole_id' => $autoEcole->id,
                    'npi' => $npi
                ], [
                    'auto_ecole_id' => $autoEcole->id,
                    'npi' => trim($npi),
                    'active' => true
                ]);
            }
            $imatriculations = explode(",", $this->validated["immatriculations"]);
            $imatriculations = array_map(function ($item) {
                return trim($item);
            }, $imatriculations);
            $imatriculations = array_filter($imatriculations);
            $vehicule_tab = $imatriculations;
            $vehicules = array_map(function ($str) {
                return [
                    'immatriculation' => $str,
                ];
            }, $vehicule_tab);


            $demandeLicence = DemandeLicence::updateOrCreate([
                'auto_ecole_id' => $autoEcole->id,
            ], [
                'promoteur_id' => $promoteur->id,
                'reference' => $agrement->code,
                'auto_ecole_id' => $autoEcole->id,
                'npi' => $promoteur->npi,
                'moniteurs' => json_encode($moniteurs),
                "vehicules" => json_encode($vehicules),
                'state' => "validate"
            ]);

            Help::historique(
                'licence',
                'Licence importée',
                'demande-licence-validate',
                $message = "L'ANaTT a importé avec succèes la dernière licence de votre auto-école {$ae->name}.",
                $promoteur,
                $demandeLicence
            );
            Licence::updateOrCreate([
                'auto_ecole_id' => $autoEcole->id,
            ], [
                'auto_ecole_id' => $autoEcole->id,
                'code' => $ae->code_licence,
                'date_debut' => $this->validated['date_licence']->addYears(),
                'date_fin' => $this->validated['date_licence'],
                'status' => $this->validated['date_licence']->isFuture()
            ]);

            $vehiculess = $demandeLicence->vehicules;
            // Enregistrer les vehicule
            foreach ($vehiculess as $vehicules) {
                Vehicule::create([
                    'immatriculation' => $vehicules['immatriculation'],
                    'auto_ecole_id' => $autoEcole->id,
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }

        /**
         * ----------------------------------------------------------------
         * On peut écrie le code qui crée les autres models qui dépendent du model groupement à partir d'ici
         * ----------------------------------------------------------------
         */
        return $this;
    }
}
                                                                                                                                                          