<?php

namespace App\Http\Controllers;

use App\Models\Admin\AnnexeAnatt;
use App\Models\Admin\Examen;
use App\Models\Candidat\DossierCandidat;
use App\Models\Candidat\DossierSession;
use App\Models\CategoriePermis;
use App\Models\Departement;
use App\Models\Permis;
use App\Services\GetCandidat;
use Illuminate\Http\Request;

class SendToAnipController extends ApiController
{
    public function getCompletedExamsData()
    {
        try {
            // Tableau associatif pour mapper les noms de départements aux identifiants correspondants
            $departmentMap = [
                'ALIBORI' => '08',
                'ATACORA' => '01',
                'ATLANTIQUE' => '02',
                'BORGOU' => '03',
                'COLLINES' => '04',
                'COUFFO' => '05',
                'DONGA' => '01',
                'LITTORAL' => '02',
                'MONO' => '05',
                'OUEME' => '06',
                'PLATEAU' => '06',
                'ZOU' => '11',
            ];

            // Obtenir tous les dossiers candidats avec un état de succès
            $successfulDossiers = DossierCandidat::successful()->get();

            // Vérifier si des dossiers candidats avec succès existent
            if ($successfulDossiers->isEmpty()) {
                return $this->errorResponse('Aucun dossier candidat trouvé avec un état de succès.', [], 404);
            }

            $resultData = [];
            $npiAdded = []; // Tableau pour garder la trace des NPI déjà ajoutés

            foreach ($successfulDossiers as $dossier) {
                // Sélectionner les informations du candidat depuis l'ANIP
                $candidat = GetCandidat::findOne($dossier->npi);
                if (!$candidat) {
                    return $this->errorResponse("Ce numéro NPI n'existe pas pour le dossier avec ID {$dossier->id}", 422);
                }

                // Obtenir les sessions associées à ce dossier
                $dossierSessions = DossierSession::where('npi', $dossier->npi)
                                                  ->where('resultat_conduite', 'success')
                                                  ->get();

                foreach ($dossierSessions as $session) {
                    // Obtenir tous les permis associés à chaque dossier de session
                    $permisList = Permis::where('npi', $session->npi)->get();

                    if ($permisList->isEmpty()) {
                        continue; // Passer au prochain dossier si aucun permis n'est trouvé
                    }

                    // Obtenir l'identifiant du département via annexe_id
                    $departmentId = $this->getDepartmentId($session->annexe_id, $departmentMap);

                    // Détails des catégories de permis avec le nom
                    $detailsCategorie = $this->getCategoryDetails($permisList);

                    // Vérifier si ce NPI a déjà été ajouté
                    if (!in_array($dossier->npi, $npiAdded)) {
                        $resultData[] = [
                            "ID_TRANSACTION" => $session->id,
                            "NPI" => $session->npi,
                            "CIP" => $session->npi, // Remplacer par l'ID CIP si disponible
                            "NOM" => data_get($candidat, 'nom'),
                            "PRENOM" => data_get($candidat, 'prenoms'),
                            "EXAM_DEPT" => $departmentId,
                            "OPERATION" => "01", // Définir une opération spécifique si nécessaire
                            "details_categorie" => $detailsCategorie, // Tableau de tous les permis
                            "BLOOD_GROUP" => $dossier->group_sanguin ?? "",
                            "NOTE" => null,
                            "ADDRESS" => data_get($candidat, 'adresse') ?? "",
                            "TELEPHONE" => data_get($candidat, 'telephone'),
                            "DOCUMENT_NUMBER" => $permisList->first()->code_permis ?? "",
                            "PREVIOUS_DRIVING_LICENCE_NUMBER" => $permisList->first()->code_permis ?? "",
                            "RETRAIT" => "00",
                            "REVOCATION" => "00",
                            "RAISON" => "",
                            "OBSERVATION" =>"",
                            "PHOTO" => "",
                        ];

                        // Ajouter le NPI au tableau des NPI ajoutés
                        $npiAdded[] = $dossier->npi;
                    }
                }
            }

            return $this->successResponse($resultData);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des données des dossiers candidats.', [], 500);
        }
    }

    // Fonction utilitaire pour obtenir l'identifiant du département
    protected function getDepartmentId($annexeId, $departmentMap)
    {
        $annexe = AnnexeAnatt::find($annexeId);
        if ($annexe) {
            $departement = Departement::find($annexe->departement_id);
            return $departmentMap[$departement->name] ?? "";
        }
        return "";
    }

    // Fonction utilitaire pour obtenir les détails des catégories
    protected function getCategoryDetails($permisList)
    {
        return $permisList->map(function ($permis) {
            $categorie = CategoriePermis::find($permis->categorie_permis_id);
            return [
                "CATEGORY" => $categorie ? $categorie->name : '', 
                "VALID_FROM" => $permis->delivered_at->format('Y-m-d'),
                "VALID_TO" => ($categorie && $categorie->name === 'B') ? "" : ($permis->expired_at ? $permis->expired_at->format('Y-m-d') : ""),
                "RESTRICTION" => $permis->restriction ?? "0"
            ];
        })->toArray();
    }
}
