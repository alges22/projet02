<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\Base\Departement;
use Illuminate\Support\Facades\Validator;

class DepartementController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->hasAnyPermission(['all', 'edit-department','read-department']);

        try {
            $query = Departement::query();

            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'ILIKE', $searchTerm);
                });
            }

            $departements = $query->orderBy('name', 'asc')->get();

            if ($departements->isEmpty()) {
                return $this->successResponse([],"Aucun résultat trouvé", 200);

            }

            return $this->successResponse($departements);
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
        $this->hasAnyPermission(['all', 'edit-department']);


        return $this->postToBase("departements", $request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->hasAnyPermission(['all', 'edit-department','read-department']);

        try {
            try {
                $departement = Departement::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le département avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($departement);
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
        $this->hasAnyPermission(['all', 'edit-department']);


        return $this->putToBase("departements/{$id}", $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(['all', 'edit-department']);

        try {
            try {
                $departement = Departement::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le département avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            $departement->delete();
            return $this->successResponse($departement, 'Le département a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
