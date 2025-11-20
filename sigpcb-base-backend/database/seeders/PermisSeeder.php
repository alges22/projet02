<?php

namespace Database\Seeders;

use App\Models\Candidat\DossierSession as CandidatDossierSession;
use Illuminate\Database\Seeder;
use App\Models\Permis;
use App\Models\DossierSession;

class PermisSeeder extends Seeder
{
    public function run()
    {
        // Récupérer tous les enregistrements de la table permis
        $permisList = Permis::all();

        foreach ($permisList as $permis) {
            // Récupérer le dossier session associé
            $dossierSession = CandidatDossierSession::find($permis->dossier_session_id);

            // Vérifier si permis_extension_id est non nul
            if ($dossierSession && $dossierSession->permis_extension_id) {
                // Créer une nouvelle entrée dans permis
                Permis::create([
                    'examen_id' => $permis->examen_id,
                    'dossier_session_id' => $permis->dossier_session_id,
                    'categorie_permis_id' => $dossierSession->permis_extension_id, 
                    'jury_candidat_id' => $permis->jury_candidat_id,
                    'npi' => $permis->npi,
                    'code_permis' => $permis->code_permis,
                    'deliver_id' => $permis->deliver_id,
                    'signed_at' => $permis->signed_at,
                    'signataire_id' => $permis->signataire_id,
                    'delivered_at' => $permis->delivered_at,
                    'expired_at' => $permis->expired_at,
                    'created_at' => $permis->created_at,
                    'updated_at' => $permis->updated_at,
                ]);
            }
        }
    }
}
