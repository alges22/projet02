<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnnexeAnattJurie;
use Illuminate\Support\Facades\Validator;

class AnnexeAnattJurieController extends ApiController
{
    public function index()
    {
        $annexejury = AnnexeAnattJurie::with(['annexe'])
            ->orderBy('id', 'desc') // Trie par ID en ordre décroissant
            ->get();
        return $this->successResponse($annexejury);
    }

    public function store(Request $request)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);
        try {
            $validator = Validator::make($request->all(), [
                'annexe_anatt_id' => 'required|integer|exists:annexe_anatts,id',
                'name' => 'required|string|unique:annexe_anatt_juries,name,NULL,id,annexe_anatt_id,' . $request->input('annexe_anatt_id')
            ], [
                "annexe_anatt_id.exists" => "Un enregistrement existe déjà pour l'annexe anatt sélectionnée.",
                "name.unique" => "Ce nom est déjà utilisé pour cette annexe anatt."
            ]);

            if ($validator->fails()) {
                return $this->sendValidatorErrors($validator);
            }

            $validatedData = $validator->validated();
            $annexejury = AnnexeAnattJurie::create($validatedData);

            return $this->successResponse($annexejury, statuscode: 201);
        }   catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la création", null, null, 500);
        }
    }


    public function update(Request $request, $id)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        try {
            // Recherche de l'enregistrement par ID
            $annexejury = AnnexeAnattJurie::find($id);

            if (!$annexejury) {
                return $this->errorResponse("Enregistrement introuvable", 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:annexe_anatt_juries,name,' . $id . ',id,annexe_anatt_id,' . $annexejury->annexe_anatt_id
            ]);

            if ($validator->fails()) {
                return $this->sendValidatorErrors($validator);
            }

            $validatedData = $validator->validated();

            // Mettez à jour les attributs avec les nouvelles données
            $annexejury->update($validatedData);

            return $this->successResponse($annexejury, statuscode: 200);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, null, 500);
        }
    }


    public function show($id)
    {
        try {
            // Recherche de l'enregistrement par ID
            $annexejury = AnnexeAnattJurie::find($id);

            if (!$annexejury) {
                return $this->errorResponse("Enregistrement introuvable", 404);
            }

            return $this->successResponse($annexejury, statuscode: 200);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de l'affichage", null, null, 500);
        }
    }

    public function getAnnexeJury($id){
        $this->hasAnyPermission(["all","read-inspections","edit-inspections"]);
        try {
            $annexejury = AnnexeAnattJurie::where('annexe_anatt_id', $id)
                                            ->orderBy('id', 'desc')
                                            ->get();

            if (!$annexejury) {
                return $this->errorResponse("Enregistrement introuvable", 404);
            }

            return $this->successResponse($annexejury, statuscode: 200);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de l'affichage", null, null, 500);
        }
    }

    public function destroy($id)
    {
        $this->hasAnyPermission(["all","edit-inspections"]);

        try {
            // Recherche de l'enregistrement par ID
            $annexejury = AnnexeAnattJurie::find($id);

            if (!$annexejury) {
                return $this->errorResponse("Enregistrement introuvable", 404);
            }

            // Supprimez l'enregistrement
            $annexejury->delete();

            return $this->successResponse("Enregistrement supprimé avec succès", statuscode: 200);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression", null, null, 500);
        }
    }


}
