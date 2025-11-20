<?php

namespace App\Http\Controllers;

use App\Services\Api;
use Illuminate\Http\Request;
use App\Models\DossierSession;
use App\Models\DossierCandidat;

class DossierSessionController extends ApiController
{


    public function index()
    {
        try {
            // Chercher la DossierSession avec l'ID donné
            $dossierSession = DossierSession::all();

            if (!$dossierSession) {
                return $this->errorResponse('Dossier Session non trouvé.', null, null, 422);
            }
            return $this->successResponse($dossierSession);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des informations.', null, null, 500);
        }
    }

    public function show($id)
    {
        $session = $this->findSession($id);
        if (!$session) {
            return $this->errorResponse("Aucun dossier session trouvé", statuscode: 404);
        }
        return $this->successResponse($session);
    }

    private function findSession($id)
    {
        $session = DossierSession::find($id);
        if (!$session) {
            return null;
        }
        $dossier = DossierCandidat::find($session->dossier_candidat_id);

        $permis = Api::data(Api::base('GET', "categorie-permis/{$dossier->categorie_permis_id}"));
        $dossier->setAttribute('categorie_permis', $permis);

        $candidatNpi = $session['npi'];
        $responseCandidat = Api::base('GET', "candidats/$candidatNpi");
        $Candidat = Api::data($responseCandidat);


        $session['candidat'] =    $Candidat;
        $session['dossier'] =    $dossier;

        return $session;
    }

    public function getInitSessionByAutoEcoleId($id)
    {
        try {

            $dossier_sessions = DossierSession::where(
                'auto_ecole_id',
                $id
            )->where('state', 'init')
                ->orderByDesc('created_at')
                ->paginate(10);

            //On ajoute le dossier et le condidat
            $dossier_sessions->map(function ($d) {
                $d->dossier = DossierCandidat::find($d->dossier_candidat_id);
                try {
                    $candidat = Api::base('GET', "candidats/{$d->npi}");
                } catch (\Exception $th) {
                    logger()->error("Le candidat ayant pour npi: $d->npi, n'a pas été trouvé ou un problème est survenu");
                }
                $d->setAttribute('candidat', Api::data($candidat));
                return $d;
            });
            // Récupérer la liste des permis depuis l'endpoint
            $permis = Api::data(Api::base('GET', "categorie-permis"));
            $result =   [];
            foreach ($dossier_sessions as $ds) {
                $dossier = $ds->dossier;
                $categoriePermisId = $dossier->categorie_permis_id;

                $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;

                $ds['nom_permis'] = $nomPermis;
                $result[] = $ds;
            }

            return $this->successResponse($result);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Impossible de récupérer la liste des dossiers des sessions");
        }
    }
}
