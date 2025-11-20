<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class InscriptionController extends ApiController
{
    public function index()
    {
        try {
            $query = Inscription::orderByDesc('created_at');

            // Appliquer les filtres
            $query = $this->applyFilters($query);
            $demandes = $query->paginate(10);
            // Obtient les npi distincts
            $npiCollection = $demandes->filter(function ($demande) {
                return !is_null($demande->npi) && $demande->npi !== '';
            })->pluck('npi')->unique();

            // Obtient les candidats en fonction des valeurs de npi
            $candidats = collect(GetCandidat::get($npiCollection->all()));

            // Associe les informations des candidats
            $demandes->each(function ($demande) use ($candidats) {
                $demandeur = $candidats->where('npi', $demande->npi)->first();
                $demande->candidat_info = $demandeur;
            });
            return $this->successResponse($demandes);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    public function applyFilters($query)
    {
        // Filtre par état (state)
        if ($auto_ecole_id = request('auto_ecole_id')) {
            $query->when(is_numeric($auto_ecole_id), function ($query) use ($auto_ecole_id) {
                $query->where('auto_ecole_id', $auto_ecole_id);
            });
        }
        // Filtre par recherche
        if ($search = request('search')) {
            $query->where('npi', 'LIKE', "%$search%");
        }
        return $query;
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required',
                'auto_ecole_id' => 'required|exists:auto_ecoles,id',
            ], [
                "npi.required" => "Le champ NPI est obligatoire.",
                "auto_ecole_id.exists" => "L'auto-école sélectionnée n'existe pas."
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }

            $existingAffiliation = Inscription::where('npi', $request->input('npi'))
                ->where('auto_ecole_id', $request->input('auto_ecole_id'))
                ->first();

            if ($existingAffiliation) {
                return $this->errorResponse('Affiliation déjà existante avec ce NPI et cette auto-école.');
            }

            $inscription = Inscription::create([
                'npi' => $request->input('npi'),
                'auto_ecole_id' => $request->input('auto_ecole_id'),
                'status' => true,
                'date_inscription' => now(),
            ]);

            return $this->successResponse($inscription, 'Affiliation effectuée avec succès', 201);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Erreur lors de la création de l\'affiliation', $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $inscription = Inscription::findOrFail($id);

            return $this->successResponse($inscription);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Inscription non trouvée', $e->getMessage(), 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $inscription = Inscription::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'npi' => 'required|unique:auto_ecole_candidat_inscriptions,npi,' . $id,
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }

            $inscription->update($request->all());

            return $this->successResponse($inscription, 'Affiliation mise à jour avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);

            return $this->errorResponse('Erreur lors de la mise à jour de l\'affiliation', $e->getMessage(), 500);
        }
    }

    public function updateState(Request $request, $id)
    {
        try {
            $inscription = Inscription::findOrFail($id);
            $inscription->update($request->all());

            return $this->successResponse($inscription, 'Statut mise à jour avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);

            return $this->errorResponse('Erreur lors de la mise à jour du statut', $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $inscription = Inscription::findOrFail($id);
            $inscription->delete();

            return $this->successResponse(null, 'Affiliation supprimée avec succès', 204);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Erreur lors de la suppression de l\'affiliation', $e->getMessage(), 500);
        }
    }
}
