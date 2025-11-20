<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\Base\CatPermisTrancheAge;
use Illuminate\Support\Facades\Validator;

class CatPermisTrancheAgeController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $cat_permis_tranche_ages = CatPermisTrancheAge::with(['categoriePermis', 'trancheAge'])->get();
            return $this->successResponse($cat_permis_tranche_ages);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|exists:base.categorie_permis,id',
                'tranche_age_validites' => 'required|array|min:1',
                'tranche_age_validites.*.tranche_age_id' => 'required|exists:base.tranche_ages,id',
                'tranche_age_validites.*.validite' => 'required|integer|min:1',
            ], [
                'categorie_permis_id.required' => 'La catégorie de permis est obligatoire.',
                'categorie_permis_id.exists' => 'La catégorie sélectionnée n\'existe.',
                'tranche_age_validites.required' => 'Au moins une tranche d\'âge avec une validité doit être spécifiée.',
                'tranche_age_validites.array' => 'La liste des tranches d\'âge et validités doit être un tableau.',
                'tranche_age_validites.min' => 'La liste des tranches d\'âge et validités doit avoir au moins un élément.',
                'tranche_age_validites.*.tranche_age_id.required' => 'La tranche d\'âge est obligatoire.',
                'tranche_age_validites.*.tranche_age_id.exists' => 'La tranche d\'âge sélectionnée n\'existe.',
                'tranche_age_validites.*.validite.required' => 'La validité est obligatoire.',
                'tranche_age_validites.*.validite.integer' => 'La validité doit être un nombre entier.',
                'tranche_age_validites.*.tranche_age_id.unique' => 'La combinaison de la catégorie de permis et de la tranche d\'âge doit être unique.',
            ]);
          
            $tranche_age_validites = collect($request->input('tranche_age_validites'));
            $duplicates = $tranche_age_validites->duplicates('tranche_age_id');
            if ($duplicates->count() > 0) {
                // Il y a des doublons
                return $this->errorResponse("Vous ne pouvez pas sélectionner deux fois la même tranche_age");
            }

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }

            $data = $validator->validated();
            $cat_permis_tranche_ages = [];
            // Supprimer les anciennes données s'il en existe
            CatPermisTrancheAge::where('categorie_permis_id', $data['categorie_permis_id'])->delete();

            foreach ($data['tranche_age_validites'] as $tranche_age_validite) {
                $cat_permis_tranche_age_data = [
                    'categorie_permis_id' => $data['categorie_permis_id'],
                    'tranche_age_id' => $tranche_age_validite['tranche_age_id'],
                    'validite' => $tranche_age_validite['validite'],
                ];
                $cat_permis_tranche_ages[] = CatPermisTrancheAge::create($cat_permis_tranche_age_data);
            }

            return $this->successResponse($cat_permis_tranche_ages, 'Catégorie Permis Tranche(s) d\'âge créée(s) avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
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
        try {
            try {
                $cat_permis_tranche_age = CatPermisTrancheAge::with(['categoriePermis', 'trancheAge'])->findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Catégorie Permis Tranche d\'âge avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            return $this->successResponse($cat_permis_tranche_age);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
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
        try {
            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|exists:base.categorie_permis,id',
                'tranche_age_id' => [
                    'required',
                    'exists:base.tranche_ages,id',
                    Rule::unique('cat_permis_tranche_ages')->where(function ($query) use ($request) {
                        return $query->where('categorie_permis_id', $request->input('categorie_permis_id'));
                    })->ignore($id)
                ],
            ], [
                'categorie_permis_id.required' => 'La catégorie de permis est obligatoire.',
                'categorie_permis_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
                'tranche_age_id.required' => 'La tranche d\'âge est obligatoire.',
                'tranche_age_id.exists' => 'La tranche d\'âge sélectionnée n\'existe pas.',
                'tranche_age_id.unique' => 'La tranche d\'âge doit être unique pour chaque combinaison de catégorie permis et tranche d\'âge.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }

            try {
                $cat_permis_tranche_age = CatPermisTrancheAge::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Catégorie Permis Tranche d\'âge avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            $cat_permis_tranche_age->update($request->all());
            return $this->successResponse($cat_permis_tranche_age, 'Catégorie Permis Tranche d\'âge mise à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
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
        try {
            try {
                $cat_permis_tranche_age = CatPermisTrancheAge::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Catégorie Permis Tranche d\'âge avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            $cat_permis_tranche_age->delete();
            return $this->successResponse($cat_permis_tranche_age, 'Catégorie Permis Tranche d\'âge a été supprimée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
