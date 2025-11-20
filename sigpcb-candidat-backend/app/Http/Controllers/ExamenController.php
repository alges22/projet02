<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Services\Api;
use App\Models\Examen;
use Illuminate\Http\Request;
use App\Models\DossierSession;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ExamenController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-candidat/examens",
     *     operationId="getAllExamen",
     *     tags={"Examen"},
     *     summary="Récupérer la liste des examens",
     *     description="Récupère une liste de tous les examens enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des candidatq récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'examen",
     *                      type="integer"
     *                  ),
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $params = [
                "closed" => false,
            ];
            $types = "extra,ordinaire";

            // Le candidat connecté
            $user = auth()->user();
            $npi = $user->npi;

            // Récupérer la session dossier
            $dossierSession = DossierSession::where([
                "npi" => $npi,
                "is_militaire" => "militaire"
            ])->latest()->first();

            // Si le candidat a une session militaire, ajouter 'militaire' aux types
            if ($dossierSession) {
                $types .= ",militaire";
            }
            $annexe_id = null;
            // Récupérer le dossier session le plus récent
            $doSession = DossierSession::where([
                "npi" => $npi,
            ])->latest()->first();

            if ($doSession) {
                $annexe_id = $doSession->annexe_id; // Récupérer l'annexe_id du candidat
                $params['notused'] = $annexe_id;    // Passer l'annexe_id dans les paramètres pour filtrer
            }

            // Ajouter le type de session (ordinaire, extra, militaire)
            $params['types'] = $types;

            // Récupérer les examens filtrés selon les paramètres
            $examens = Examen::filter($params)->orderBy('date_code')->get();

            // Mappez chaque examen pour inclure les données d'agenda
            $agendas = $examens->map(fn(Examen $agenda) => $agenda->asAgenda());

            // Filtrer les examens dont la date_code est aujourd'hui ou dans le futur
            $filteredData =  $agendas->filter(function ($examen) use ($annexe_id) {
                // Ajouter un jour pour que la date soit considérée comme "future"
                $dateCode = Carbon::parse($examen['date_code'])->addDay();

                // Filtrer les examens dont la date_code est dans le futur et dont l'annexe_id correspond
                return !$dateCode->isPast() && in_array($annexe_id, $examen['annexe_ids'] ?? []);
            });

            // Retourner les résultats filtrés
            return $this->successResponse($filteredData->values());
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des examens.', 500);
        }
    }

}
