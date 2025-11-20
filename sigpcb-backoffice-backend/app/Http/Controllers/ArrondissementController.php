<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\Base\Arrondissement;
use Illuminate\Support\Facades\Validator;

class ArrondissementController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->hasAnyPermission(['all', "read-arrondissement","edit-arrondissement"]);

        try {
            $query = Arrondissement::query()->with('commune');

            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'ILIKE', $searchTerm)
                        ->orWhereHas('commune', function ($query) use ($searchTerm) {
                            $query->where('name', 'ILIKE', $searchTerm);
                        });
                });
            }

            // Ajout du GROUP BY sur departement_id
            $query->orderBy('commune_id', 'asc')->groupBy(['commune_id', 'id']);

            if (request('liste') == 'paginate') {
                $arrondissements = $query->orderBy('name', 'asc')->paginate(10);
            } else {
                $arrondissements = $query->orderBy('name', 'asc')->get();
            }

            if ($arrondissements->isEmpty()) {
                return $this->successResponse([],"Aucun résultat trouvé", 200);
            }

            return $this->successResponse($arrondissements);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
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
        $this->hasAnyPermission(['all', 'edit-arrondissement']);

        return $this->postToBase("arrondissements", $request->all());
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->hasAnyPermission(['all', "read-arrondissement","edit-arrondissement"]);

        try {
            try {
                $arrondissement = Arrondissement::with(['commune'])->findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'arrondissement avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($arrondissement);
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
        $this->hasAnyPermission(['all', 'edit-arrondissement']);


        return $this->putToBase("arrondissements/{$id}", $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(['all', 'edit-arrondissement']);


        try {
            try {
                $arrondissement = Arrondissement::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'arrondissement avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 404);
            }
            $arrondissement->delete();
            return $this->successResponse($arrondissement, 'L\'arrondissement a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
