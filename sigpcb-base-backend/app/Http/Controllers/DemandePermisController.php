<?php

namespace App\Http\Controllers;

use App\Models\DemandePermis;
use App\Models\Permis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DemandePermisController extends ApiController
{
    public function store(Request $request)
    {
        DB::beginTransaction(); // Début de la transaction

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'categorie_permis_id' => 'required|exists:categorie_permis,id',
                'npi' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendValidatorErrors($validator);
            }

            $categorie_permis_id = $request->input('categorie_permis_id');
            $npi = $request->input('npi');
            $validatedData = $validator->validated();

            $existingRecord = Permis::where('npi', $npi)
                ->where('categorie_permis_id', $categorie_permis_id)
                ->first();
            if (!$existingRecord) {
                DB::rollBack();
                return $this->errorResponse('Vous devez avoir réussi à cette catégorie de permis avant de demander ce service.', null, null, 422);
            }
            // }

            $demande = DemandePermis::create($validatedData);
            DB::commit(); // Valider la transaction si tout s'est bien passé
            return $this->successResponse($demande, statuscode: 201);
        } catch (\Throwable $e) {
            DB::rollBack(); // Annuler la transaction en cas d'exception
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la création", null, null, 500);
        }
    }

    public function checkPermis(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|exists:categorie_permis,id',
                'npi' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendValidatorErrors($validator);
            }

            $categorie_permis_id = $request->input('categorie_permis_id');
            $npi = $request->input('npi');

            $existingRecord = Permis::where('npi', $npi)
                ->where('categorie_permis_id', $categorie_permis_id)
                ->first();
            if (!$existingRecord) {
                return $this->errorResponse('Vous devez avoir réussi a cette catégorie de permis avant de demander ce service.', null, null, 404);
            }
            return $this->successResponse($existingRecord, statuscode: 200);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la vérification", null, null, 500);
        }
    }

    public function getUserPermis(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendValidatorErrors($validator);
            }

            $npi = $request->input('npi');
            $existingRecord = Permis::with('categoriePermis')
                                        ->where('npi', $npi)
                                        ->get();
            return $this->successResponse($existingRecord, statuscode: 200);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la vérification", null, null, 500);
        }
    }
}
