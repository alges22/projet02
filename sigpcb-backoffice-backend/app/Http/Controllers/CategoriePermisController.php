<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\Base\CategoriePermis;
use App\Models\Base\CategoriePermisExtensible;
use App\Models\Base\SalleCompo;
use App\Models\Base\TrancheAge;
use Illuminate\Support\Facades\Validator;

class CategoriePermisController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->hasAnyPermission(["all", "read-permit-categories-management","edit-permit-categories-management"]);

        try {
            $categorie_permis = CategoriePermis::with(['trancheage', 'extensions', 'permisPrealable'])
                ->orderBy('name', 'asc')
                ->get();

            return $this->successResponse($categorie_permis);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getExtension()
    {
        $this->hasAnyPermission(["all", "read-permit-categories-management","edit-permit-categories-management"]);

        try {
            // Utiliser la méthode where pour filtrer les données où le champ is_extension est égal à true
            $categorie_permis = CategoriePermis::with(['trancheage', 'permisPrealable'])->where('is_extension', true)->orderBy('name', 'asc')->get();
            return $this->successResponse($categorie_permis);
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
        $this->hasAnyPermission(["all","edit-permit-categories-management"]);


        return $this->postToBase("categorie-permis", $request->all());
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        try {
            try {
                $categorie_permis = CategoriePermis::with(['trancheage', 'extensions', 'permisPrealable'])->findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La catégorie de permis avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            return $this->successResponse($categorie_permis);
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
    public function update($id, Request $request)
    {
        $this->hasAnyPermission(["all","edit-permit-categories-management"]);

        return $this->putToBase("categorie-permis/{$id}", $request->all());
    }

    public function updateSalle($id, Request $request)
    {
        $this->hasAnyPermission(["all", "edit-annex-management"]);

        return $this->putToBase("salle-compos/{$id}", $request->all());
    }
    public function deleteSalle($id)
    {
        try {
            // Recherche de la salle de composition par ID
            $salle_compo = SalleCompo::findOrFail($id);

            // Tentative de suppression de la salle
            $salle_compo->delete();

            // Retour succès après suppression
            return $this->successResponse($salle_compo, 'La salle de composition a été supprimée avec succès.');

        } catch (ModelNotFoundException $exception) {
            // Si la salle n'est pas trouvée, message d'erreur
            return $this->errorResponse('La salle de composition avec l\'ID '.$id.' n\'a pas été trouvée.', [], null, 404);
        } catch (\Illuminate\Database\QueryException $exception) {
            // Si une contrainte de clé étrangère empêche la suppression
            if ($exception->getCode() == '23503') {  // Code d'erreur spécifique aux violations de contrainte de clé étrangère
                return $this->errorResponse('La salle de composition ne peut pas être supprimée car elle est utilisée ailleurs.', [], null, 400);
            }
            // Si une autre erreur de requête se produit
            logger()->error($exception);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        } catch (\Throwable $th) {
            // Gestion des erreurs générales
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
        $this->hasAnyPermission(["all","edit-permit-categories-management"]);


        try {
            try {
                $categorie_permis = CategoriePermis::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La catégorie de permis avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            // Supprimer les informations de la relation "extensions"
            $categorie_permis->extensions()->delete();
            // Delete related "trancheage"
            $categorie_permis->chapitres()->delete();
            $categorie_permis->trancheage()->delete();
            $categorie_permis->baremes()->delete();

            // Delete the "CategoriePermis" record
            $categorie_permis->delete();

            return $this->successResponse($categorie_permis, 'La catégorie de permis a été supprimée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    public function storeExtension(Request $request)
    {
        $this->hasAnyPermission(["all","edit-permit-categories-management"]);


        try {
            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|exists:base.categorie_permis,id',
                'categorie_permis_extensible_id' => 'required|exists:base.categorie_permis,id',
            ], [
                'categorie_permis_id.required' => 'La catégorie de permis est obligatoire.',
                'categorie_permis_extensible_id.required' => 'L\extension est obligatoire.',
                'categorie_permis_id.exists' => 'La catégorie de permis sélectionnée n\'existe pas.',
                'categorie_permis_extensible_id.exists' => 'L\'extension sélectionnée n\'existe pas.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), 422);
            }

            $categorie_permis_id = $request->input('categorie_permis_id');
            $categorie_permis_extensible_id = $request->input('categorie_permis_extensible_id');

            // Vérifier si l'insertion existe déjà
            $existingExtension = CategoriePermisExtensible::where('categorie_permis_id', $categorie_permis_id)
                ->where('categorie_permis_extensible_id', $categorie_permis_extensible_id)
                ->first();

            if ($existingExtension) {
                return $this->errorResponse('Cette extension existe déjà dans la base de données.');
            }

            $extension = CategoriePermisExtensible::create($request->all());
            return $this->successResponse($extension, 'Extension créée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    public function destroyExtension($id)
    {
        $this->hasAnyPermission(["all","edit-permit-categories-management"]);


        try {
            try {
                $extension = CategoriePermisExtensible::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'extension avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            $extension->delete();
            return $this->successResponse($extension, 'L\'extension de la catégorie de permis a été supprimée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
