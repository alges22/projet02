<?php

namespace App\Http\Controllers;

use App\Services\Api;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\Base\CategoriePermis;
use App\Models\Base\BaremeConduite;
use Illuminate\Support\Facades\Validator;

class BaremeConduiteController extends ApiController
{
    public function getBaremeConduiteByPermis($categorie_permis_id)
    {
        $this->hasAnyPermission(["all", "edit-baremes-management","read-baremes-management"]);

        try {
            $path = "bareme-conduites/categorie-permis/" .$categorie_permis_id;
            $response = Api::base('GET', $path);

            $data = Api::data($response);

            if ($data === -1) {
                return $this->errorResponse('Aucun résultat trouvé', 404);
            }

            return $this->successResponse($data);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération', 500);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-baremes-management","read-baremes-management"]);

        try {
            $query = CategoriePermis::query()->with('baremes');

            // if ($request->has('search')) {
            //     $searchTerm = '%' . $request->input('search') . '%';
            //     $query->where(function ($query) use ($searchTerm) {
            //         $query->whereRaw('LOWER(name) LIKE ?', [strtolower($searchTerm)]);
            //     });
            // }

            $categorie_permis = $query->orderBy('name', 'asc')->get();
            if ($categorie_permis->isEmpty()) {
                return $this->successResponse([],"Aucun résultat trouvé", 200);
            }

            return $this->successResponse($categorie_permis);
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
        $this->hasAnyPermission(["all", "edit-baremes-management"]);

        return $this->postToBase("bareme-conduites", $request->all());

    }


    public function addBareme(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-baremes-management"]);


        return $this->postToBase("bareme-conduite", $request->all());

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
                $bareme_conduite = BaremeConduite::findOrFail($id);

            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le barème de conduite avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($bareme_conduite);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    public function getByCategoriePermisId($categorie_permis_id)
    {
        try {
            // Récupérer la catégorie de permis par son ID avec ses relations d'insertions
            $categorie_permis = CategoriePermis::with(['baremes' => function ($query) {
                $query->orderBy('id');
            }])->find($categorie_permis_id);

            // Vérifier si la catégorie de permis existe
            if (!$categorie_permis) {
                return $this->errorResponse('Catégorie de permis introuvable', null, null, 404);
            }

            // Retourner la catégorie de permis avec ses insertions triées
            return $this->successResponse($categorie_permis);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
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
        $this->hasAnyPermission(["all", "edit-baremes-management"]);


        return $this->putToBase("bareme-conduites/{$id}", $request->all());

    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(["all", "edit-baremes-management"]);


        try {
            try {
                $bareme_conduite = BaremeConduite::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le barème de conduite avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            $bareme_conduite->delete();
            return $this->successResponse($bareme_conduite, 'Le barème de conduite a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
