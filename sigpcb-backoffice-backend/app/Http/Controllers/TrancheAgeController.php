<?php

namespace App\Http\Controllers;

use App\Models\Base\CategoriePermis;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\Base\TrancheAge;
use Illuminate\Support\Facades\Validator;

class TrancheAgeController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $tranche_ages = TrancheAge::orderBy('id', 'DESC')->get();
            return $this->successResponse($tranche_ages);
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
            Validator::extend('champ1_inferieur_champ2', function($attribute, $value, $parameters, $validator) {
                $champ1 = $value;
                $champ2 = $validator->getData()[$parameters[0]];
                return $champ1 < $champ2;
            });

            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|exists:base.categorie_permis,id',
                'validite' => 'required|integer',
                'age_min' => [
                    'required',
                    'integer',
                    'champ1_inferieur_champ2:age_max',
                    Rule::unique('tranche_ages')->where(function ($query) use ($request) {
                        return $query->where('age_max', $request->input('age_max'));
                    })->ignore($request->input('id'), 'id')
                ],
                'age_max' => 'required|integer',
                'status' => 'boolean',
            ], [
                'categorie_permis_id.required' => 'L\'ID de la catégorie de permis est obligatoire.',
                'categorie_permis_id.exists' => 'L\'ID de la catégorie de permis spécifié n\'existe pas.',
                'validite.required' => 'La validité du permis est obligatoire.',
                'age_min.required' => 'L\'âge minimal est obligatoire.',
                'age_min.integer' => 'L\'âge minimal doit être un entier.',
                'age_max.required' => 'L\'âge maximal est obligatoire.',
                'age_max.integer' => 'L\'âge maximal doit être un entier.',
                'champ1_inferieur_champ2' => 'L\'âge minimal doit être inférieur à l\'âge maximal.',
                'age_min.unique' => 'L\'âge minimal doit être unique pour chaque combinaison d\'âge minimal et d\'âge maximal.',
                'status.boolean' => 'Le statut doit être une valeur booléenne.',
            ]);
            $categorie_permis_id = $request->input('categorie_permis_id');

            $existingAgeMin = CategoriePermis::where('id', $categorie_permis_id)->value('age_min');

            if ($request->input('age_min') < $existingAgeMin) {
                return $this->errorResponse("La tranche d'âge [{$request->input('age_min')}, {$request->input('age_max')}] est invalide. L'âge minimal ne doit pas être inférieur à l'âge minimal du permis.", null, 422);
            }


            if ($validator->fails()) {
                return $this->errorResponse('La validation a échoué.', $validator->errors(),422);
            }
            $data = $validator->validated();
            $existingTrancheAge = TrancheAge::where([
                'categorie_permis_id' => $data['categorie_permis_id'],
                'validite' => $data['validite'],
                'age_min' => $data['age_min'],
                'age_max' => $data['age_max'],
            ])->first();

            if ($existingTrancheAge) {
                return $this->errorResponse('Une tranche d\'âge identique existe déjà.', [], 409);
            }
            $tranche_age = TrancheAge::create($data);
            return $this->successResponse($tranche_age, 'Tranche d\'âge créée avec succès.');
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
                $tranche_ages = TrancheAge::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La tranche d\âge avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($tranche_ages);
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

            Validator::extend('champ1_inferieur_champ2', function($attribute, $value, $parameters, $validator) {
                $champ1 = $value;
                $champ2 = $validator->getData()[$parameters[0]];
                return $champ1 < $champ2;
            });

            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|exists:base.categorie_permis,id',
                'age_min' => [
                    'nullable',
                    'integer',
                    'champ1_inferieur_champ2:age_max',
                    Rule::unique('tranche_ages')->where(function ($query) use ($request) {
                        return $query->where('age_max', $request->input('age_max'));
                    })->ignore($id)
                ],
                'age_max' => 'nullable|integer'
            ], [
                'age_min.required' => 'L\'âge minimal est obligatoire.',
                'age_max.required' => 'L\'âge maximal est obligatoire.',
                'champ1_inferieur_champ2' => 'L\'âge minimal doit être plus petit que l\'âge maximal.',
                'age_min.unique' => 'L\'âge minimal doit être unique pour chaque combinaison d\'âge maximal et d\'âge minimal.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }
            $categorie_permis_id = $request->input('categorie_permis_id');

            $existingAgeMin = CategoriePermis::where('id', $categorie_permis_id)->value('age_min');

            if ($request->input('age_min') < $existingAgeMin) {
                return $this->errorResponse("La tranche d'âge [{$request->input('age_min')}, {$request->input('age_max')}] est invalide. L'âge minimal ne doit pas être inférieur à l'âge minimal du permis.", null, 422);
            }
            try {
                $tranche_age = TrancheAge::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La tranche d\'âge avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            $tranche_age->update($request->all());
            return $this->successResponse($tranche_age, 'Tranche d\'âge mise à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    public function updateTrancheAge(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'age_min' => 'required|integer|min:1',
                'validite' => 'required|min:1',
                'age_max' => 'required|integer|min:1',
                'categorie_permis_id' => 'required|exists:base.categorie_permis,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }

            try {
                $tranche_age = TrancheAge::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La tranche age avec l\'ID '.$id.' n\'a pas été trouvée.', [], null, 404);
            }

            // Vérifier si les nouvelles valeurs existent déjà pour cette catégorie de permis
            if ($request->has('age_min') && $request->has('validite') && $request->has('age_max') && $request->has('categorie_permis_id')) {
                $existingTrancheAge = TrancheAge::where('age_min', $request->input('age_min'))
                    ->where('validite', $request->input('validite'))
                    ->where('age_max', $request->input('age_max'))
                    ->where('categorie_permis_id', $request->input('categorie_permis_id'))
                    ->first();

                if ($existingTrancheAge && $existingTrancheAge->id != $id) {
                    return $this->errorResponse('Une tranche d\'âge avec les mêmes valeurs existe déjà pour cette catégorie de permis.', [], null, 422);
                }
            }

            $tranche_age->update($request->all());
            return $this->successResponse($tranche_age, 'Tranche age mise à jour avec succès.');
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
                $tranche_age = TrancheAge::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La tranche d\'âge avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            $tranche_age->delete();
            return $this->successResponse($tranche_age, 'La tranche d\'âge a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    private function tranchageExist(string $tranche_age_ids)
    {
        $ids = explode(";", $tranche_age_ids);
        return collect($ids)->every(fn ($id) => TrancheAge::whereId(intval($id))->exists());
    }

    public function status(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tranche_age_id' => 'required',
                'status' => 'required'
            ], [
                'tranche_age_id.required' => 'Aucune tranche d\'age n\'a été sélectionné',
                'status.required' => 'Aucun statut n\'a été envoyé'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), 422);
            }

            $tranche_age_id = $request->get('tranche_age_id');
            $status = $request->get('status');
            if (!$this->tranchageExist($tranche_age_id)) {
                return $this->errorResponse('Vérifiez que la tranche d\'age sélectionné existe', $validator->errors(),422);
            }

            $tranche_age = TrancheAge::where('id', $tranche_age_id)->first();

            TrancheAge::where('id', $tranche_age_id)->update(['status' => $status]);
            $tranche_age = TrancheAge::findOrFail($tranche_age_id); // récupérer la langue mis à jour
            return $this->successResponse(['tranche_age' => $tranche_age, 'message' => 'Mise à jour effectué avec succès']);
        } catch (\Throwable $th) {
            // Gérer l'erreur et la journaliser
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour");
        }
    }
}
