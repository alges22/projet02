<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\Base\Commune;
use Illuminate\Support\Facades\Validator;

class CommuneController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->hasAnyPermission(['all', 'edit-commune',"read-commune"]);

        try {
            $query = Commune::query()->with('departement');

            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'ILIKE', $searchTerm)
                        ->orWhereHas('departement', function ($query) use ($searchTerm) {
                            $query->where('name', 'ILIKE', $searchTerm);
                        });
                });
            }

            // Ajout du GROUP BY sur departement_id et id
            $query->orderBy('departement_id', 'asc')->groupBy(['departement_id', 'id']);

            if (request('liste') == 'paginate') {
                $communes = $query->orderBy('name', 'asc')->paginate(10);
            } else {
                $communes = $query->orderBy('name', 'asc')->get();
            }

            if ($communes->isEmpty()) {
                return $this->successResponse([],"Aucun résultat trouvé", 200);
            }

            return $this->successResponse($communes);
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
        $this->hasAnyPermission(['all', 'edit-commune']);
        return $this->postToBase("communes", $request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->hasAnyPermission(['all', 'edit-commune',"read-commune"]);

        try {
            try {
                $commune = Commune::with(['departement'])->findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La commune avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($commune);
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
        $this->hasAnyPermission(['all', 'edit-commune']);

        return $this->putToBase("communes/{$id}", $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(['all', 'edit-commune']);

        try {
            try {
                $commune = Commune::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La commune avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            $commune->delete();
            return $this->successResponse($commune, 'La commune a été supprimée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
