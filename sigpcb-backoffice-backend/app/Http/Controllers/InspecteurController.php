<?php

namespace App\Http\Controllers;

use App\Services\Api;
use App\Models\Inspecteur;
use App\Models\AnnexeAnatt;
use Illuminate\Http\Request;
use App\Models\InspecteurSalle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InspecteurController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-admin/inspecteurs",
     *     operationId="getAllInspecteurs",
     *     tags={"Inspecteurs"},
     *     summary="Récupérer la liste des inspecteurs",
     *     description="Récupère une liste de tous les inspecteurs enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des inspecteurs récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'inspecteur",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="user_id",
     *                      description="ID de l'utilisateur associé à l'inspecteur",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="annexe_anatt_id",
     *                      description="ID de l'annexe associé à l'inspecteur",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="agent_id",
     *                      description="ID de l'agent ayant associé l'inspecteur",
     *                      type="integer"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $this->hasAnyPermission(["all","read-inspections","edit-inspections"]);

        $inspecteurs = Inspecteur::with(['user', 'annexe'])
            ->orderBy('id', 'desc') // Trie par ID en ordre décroissant
            ->get();
        return $this->successResponse($inspecteurs);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|unique:inspecteurs,user_id',
                'annexe_anatt_id' => 'required|integer',
            ]);

            // Obtenir l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $agent_id = $user->id; // Récupérer l'ID de l'utilisateur connecté

            if ($validator->fails()) {
                return $this->sendValidatorErrors($validator);
            }

            // Ajouter l'agent_id aux données validées
            $validatedData = $validator->validated();
            $validatedData['agent_id'] = $agent_id;

            $inspecteur = Inspecteur::create($validatedData);
            return $this->successResponse($inspecteur, statuscode: 201);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la création", null, null, 500);
        }
    }


    public function assignInspecteur(Request $request)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        $rules = [
            'inspecteur_ids' => 'required|array',
            'salle_compo_id' => 'required|integer',
            'examen_id' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendValidatorErrors($validator);
        }

        $collection = collect($request->inspecteur_ids);
        $examenId = $request->examen_id;
        $salleCompoId = $request->salle_compo_id;

        # Vérifier que tous les inspecteurs existent
        $canCreate = $collection->every(function ($inspecteur_id) {
            return Inspecteur::where('id', $inspecteur_id)->exists();
        });

        if ($canCreate) {
            // Vérifier si le couple examen_id et salle_compo_id existe
            $existingRecord = InspecteurSalle::where([
                "examen_id" => $examenId,
                "salle_compo_id" => $salleCompoId
            ])->exists();

            if ($existingRecord) {
                // Supprimer les enregistrements existants
                InspecteurSalle::where([
                    "examen_id" => $examenId,
                    "salle_compo_id" => $salleCompoId
                ])->delete();
            }

            // Insérer les nouveaux enregistrements
            foreach ($collection->all() as $key => $inspecteurId) {

            // Vérifier si l'examinateur est deja associé a une autre salle pour cet examen
            $existingAsign = InspecteurSalle::where([
                "inspecteur_id" => $inspecteurId,
                "examen_id" => $examenId,
            ])->exists();
            if($existingAsign){
                return $this->errorResponse("Un des inspecteurs sélectionnés est déjà associé a une autre salle.", 404);
            }

            $exisdtingAsign = InspecteurSalle::where([
                "inspecteur_id" => $inspecteurId,
                "examen_id" => $examenId,
                "salle_compo_id" => $salleCompoId
            ])->exists();
            if($exisdtingAsign){
                return $this->errorResponse("Un des inspecteurs sélectionnés est déjà associé a cette  salle.", 404);
            }

                InspecteurSalle::create([
                    "inspecteur_id" => $inspecteurId,
                    "examen_id" => $examenId,
                    "salle_compo_id" => $salleCompoId
                ]);
            }

            $created = InspecteurSalle::where([
                "examen_id" => $examenId,
                "salle_compo_id" => $salleCompoId
            ])->get();

            return $this->successResponse($created);
        } else {
            return $this->errorResponse("Un des inspecteurs sélectionnés n'existe pas ou a été supprimé, veuillez réessayer.", statuscode: 404);
        }
    }



    public function inspecteursBySalle($salle_compo_id)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        try {
            $inspecteurs = InspecteurSalle::where('salle_compo_id', $salle_compo_id)
                ->orderBy('id', 'desc')
                ->get()
                ->groupBy('examen_id'); // Regrouper par examen_id

            $result = [];

            foreach ($inspecteurs as $examenId => $inspecteurCollection) {
                $inspecteurIds = $inspecteurCollection->pluck('id')->toArray();
                $result[] = [
                    'examen_id' => $examenId,
                    'inspecteurs' => $inspecteurIds,
                ];
            }

            return $this->successResponse($result);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, null, 500);
        }
    }

    public function inspecteursBySalleAndSession(Request $request)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        try {
            $rules = [
                'salle_compo_id' => 'required|integer',
                'examen_id' => 'required|integer',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->sendValidatorErrors($validator);
            }
            $salle_compo_id = $request->input('salle_compo_id');
            $examen_id = $request->input('examen_id');

            $inspecteurs = InspecteurSalle::where('salle_compo_id', $salle_compo_id)
                                            ->where('examen_id', $examen_id)
                                            ->orderBy('id', 'desc')
                                            ->get();

            return $this->successResponse($inspecteurs);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, null, 500);
        }
    }

    public function getAssignation($id)
        {
                try {
                        $path = "annexe-anatts/" . $id . "/salles-inspecteurs";
                        $response =  Api::base('GET', $path);
                        if ($response->ok()) {
                        $responseData = $response->json();
                        return $this->successResponseclient($responseData, 'Success', 200);
                        } else {
                        $errorData = $response->json();
                        $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la récupération';
                        $errors = $errorData['errors'] ?? null;
                        return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
                        }
                } catch (\Throwable $th) {
                        logger()->error($th);
                        return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
                }
        }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $inspecteur = Inspecteur::find($id);
        if (!$inspecteur) {
            return $this->errorResponse('Inspecteur introuvable', null, null, 422);
        }
        return $this->successResponse($inspecteur);
    }


    public function inspecteursByAnnexe($annexe_id)
    {
        try {
            $inspecteurs = Inspecteur::with(['user', 'annexe'])
                ->where('annexe_anatt_id', $annexe_id)
                ->orderBy('id', 'desc')
                ->get();

            return $this->successResponse($inspecteurs);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        try {
            $inspecteur = Inspecteur::find($id);
            if (!$inspecteur) {
                return $this->errorResponse('Inspecteur introuvable', null, null, 422);
            }
            $validator = Validator::make($request->all(), [
                'user_id' => 'nullable|integer',
                'annexe_anatt_id' => 'nullable|integer',
            ]);
            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', null, null, 422);
            }
            $inspecteur->update($validator->validated());
            return $this->successResponse($inspecteur, 'Mise à jour effectuée avec succès');
        } catch (\Throwable $e) {
            // Log error here
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        try {
            $inspecteur = Inspecteur::find($id);
            if (!$inspecteur) {
                return $this->errorResponse('Inspecteur introuvable', null, null, 422);
            }
            $inspecteur->delete();
            return $this->successResponse(['message' => 'Suppression effectuée']);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression");
        }
    }
}
