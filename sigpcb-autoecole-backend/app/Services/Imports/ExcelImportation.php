<?php

namespace App\Services\Imports;

use App\Models\User;
use App\Services\Help;
use App\Models\Commune;
use App\Models\Licence;
use App\Models\Agrement;
use App\Models\Moniteur;
use App\Models\AutoEcole;
use App\Models\Historique;
use App\Models\Departement;
use Illuminate\Support\Str;
use App\Models\OldAutoEcole;
use App\Models\DemandeLicence;
use Illuminate\Support\Carbon;
use App\Models\DemandeAgrement;
use Illuminate\Support\Facades\DB;
use App\Services\Imports\FromExcelRow;
use App\Services\Exception\ImportationException;

class ExcelImportation
{
    public function __construct(private FromExcelRow $row)
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
        DB::beginTransaction();
        try {
            $communeName = strtoupper(trim($this->row->getCommune()));
            $departementName = strtoupper(trim($this->row->getDepartement()));

            $commune = $this->findCommune($communeName);

            if (!$commune) {
                throw new ImportationException("La commune {$communeName} n'existe pas pour l'auto-école {$this->row->getAutoEcole()} ou elle est peut-être mal écrite",);
            }

            $departement = $this->findDepartement($departementName);

            if (!$departement) {
                throw new ImportationException("Le département {$departementName} n'existe pas pour l'auto-école {$this->row->getAutoEcole()} ou elle est peut-être mal écrit",);
            }


            $fillable = [
                "name" => trim($this->row->getAutoEcole()),
                'departement' => trim($this->row->getDepartement()),
                'commune' => trim($this->row->getCommune()),
                'moniteur_npis' => trim($this->row->getNpisDesMoniteursSeparesPar()),
                'promoteur_npi' => trim($this->row->getNpiDuPromoteur()),
                'adresse' => trim($this->row->getAdresseQuartierilotparcelle()),
                'agrement' => trim($this->row->getReferenceAutorisation()),
                'expire_licence' => trim($this->row->getDateExpirationDeLaLicence()),
                'code_licence' => trim($this->row->getCodeLicence()),
                'ifu' => trim($this->row->getIfu()),
                'email_pro' => trim($this->row->getEMailProfessionnel()),
                'email_promoteur' => trim($this->row->getEMailProfessionnel()),
                'telephone_pro' => trim($this->row->getTelephoneProfessionnel()),
                'vehicules' => trim($this->row->getVehiculesDeLautoEcole())
            ];
            $ae = OldAutoEcole::updateOrCreate([
                'name' => $this->row->getAutoEcole(),
            ], $fillable);

            $moniteurs = array_map('trim', explode(',', $ae->moniteur_npis));

            if (count($moniteurs) < 2) {
                throw new ImportationException("Les moniteurs de l'auto-école  {$this->row->getAutoEcole()} n'atteignent pas 2 ou peut-être leurs NPIs sont mal renseignés",);
            }

            $promoteur =  User::updateOrCreate([
                "npi" => $this->row->getNpiDuPromoteur()
            ], [
                'npi' => $this->row->getNpiDuPromoteur(),
                'email' => $this->row->getEMailDuPromoteur(),
            ]);


            if (!DemandeAgrement::where([
                'ifu' => $ae->ifu,
                "auto_ecole" => $ae->name,
            ])->exists()) {
                if (DemandeAgrement::whereIfu($ae->ifu)->exists()) {
                    throw new ImportationException("Ce numéro IFU est déjà, l'auto-écle {$this->row->getAutoEcole()} ne peut plus l'utiliser",);
                }
            }

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
                'state' => "validate"
            ]);

            Help::historique(
                'agrement',
                'Agrément importée',
                'demande-agrement-validate',
                $message = "L'ANaTT a importé avec succèes votre l'agrément de votre auto-école {$ae->name}.",
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




            $autoEcole = AutoEcole::updateOrCreate([
                'agrement_id' => $agrement->id,
            ], [
                'agrement_id' => $agrement->id,
                'name' => $ae->name,
                'email' => $ae->email_pro,
                'phone' => $ae->telephone_pro,
                'code' => substr($ae->telephone_pro, 0, 6),
                'num_ifu' => $ae->ifu,
                'promoteur_id' => $promoteur->id,
                'departement_id' => $departement->id,
                'commune_id' => $commune->id,
                'adresse' => $ae->adresse,
                'imported' => true
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


            $vehicule_tab = explode(',', $ae->vehicules);

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
                'npi' => $promoteur->id,
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
                'date_debut' => $this->createCarbonDate($ae->expire_licence)->addYears(),
                'date_fin' => $this->createCarbonDate($ae->expire_licence),
                'status' => true
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        /**
         * ----------------------------------------------------------------
         * On peut écrie le code qui crée les autres models qui dépendent du model groupement à partir d'ici
         * ----------------------------------------------------------------
         */
        return $this;
    }

    private function findDepartement($name)
    {
        $cq =  Departement::where('name', 'like', "%$name%");

        if ($cq->count() > 1 || $cq->count() == 0) {
            return Departement::all()->filter(function ($c) use ($name) {
                return Str::slug($c->name) == Str::slug($name);
            })->first();
        }

        return $cq->first();
    }

    private function findCommune($name)
    {
        $cq =  Commune::where('name', 'like', "%$name%");

        if ($cq->count() > 1 || $cq->count() == 0) {
            return Commune::all()->filter(function ($c) use ($name) {
                return Str::slug($c->name) == Str::slug($name);
            })->first();
        }

        return $cq->first();
    }

    private function createCarbonDate($date_str): Carbon
    {
        if (is_numeric($date_str)) {

            return Carbon::createFromDate(1900, 1, 1)->addDays($date_str - 2);
        }
        // Remplace toutes les occurrences de "/" par "-"
        $date_str = str_replace('/', '-', $date_str);
        $carbonDate = \Carbon\Carbon::createFromFormat('d-m-Y', $date_str);

        return $carbonDate;
    }
}
