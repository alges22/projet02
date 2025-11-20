<?php

namespace App\Http\Controllers;

use App\Models\Base\SubBareme;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;

class SubBaremeController extends ApiController
{
      // Fonction pour créer un sous-barème
      public function store(Request $request)
      {
        $this->hasAnyPermission(["all","edit-qcm-management"]);

          try {
              $request->validate([
                  'name' => 'required|string|unique:base.sub_baremes,name',
                  'bareme_conduite_id' => 'required|exists:base.bareme_conduites,id',
                  'eliminatoire' => 'boolean',
              ]);

              // Créer un tableau de données à partir de la requête
              $data = $request->only(['name', 'bareme_conduite_id', 'eliminatoire']);

              // Créer le sous-barème
              $subBareme = SubBareme::create($data);

              return $this->successResponse($subBareme, "Sous-barème créé avec succès.");
          } catch (\Throwable $e) {
              logger()->error($e);
              return $this->errorResponse("Erreur lors de la création du sous-barème.", [$e->getMessage()]);
          }
      }


      // Fonction pour afficher un sous-barème
      public function show($id)
      {

          try {
              $subBareme = SubBareme::findOrFail($id);
              return $this->successResponse($subBareme, "Sous-barème récupéré avec succès.");
          } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            logger()->error($e);
              return $this->errorResponse("Sous-barème non trouvé.", [], null, 422);
          } catch (\Exception $e) {
            logger()->error($e);
              return $this->errorResponse("Erreur lors de la récupération du sous-barème.", [$e->getMessage()]);
          }
      }

      // Fonction pour supprimer un sous-barème
      public function destroy($id)
      {
        $this->hasAnyPermission(["all","edit-qcm-management"]);

          try {
              $subBareme = SubBareme::findOrFail($id);
              $subBareme->delete();
              return $this->successResponse(null, "Sous-barème supprimé avec succès.");
          } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
              logger()->error($e);
              return $this->errorResponse("Sous-barème non trouvé.", [], null, 422);
          } catch (\Illuminate\Database\QueryException $e) {
              // Erreur de contrainte d'intégrité (par ex., clé étrangère)
              if ($e->getCode() == 23000) {
                  return $this->errorResponse("Impossible de supprimer le sous-barème car il est utilisé ailleurs.", [], null, 409);
              }
              return $this->errorResponse("Erreur lors de la suppression du sous-barème.", [$e->getMessage()]);
          } catch (\Exception $e) {
              return $this->errorResponse("Erreur lors de la suppression du sous-barème.", [$e->getMessage()]);
          }
      }


      public function update(Request $request, $id)
      {
        $this->hasAnyPermission(["all","edit-qcm-management"]);

          try {
              // Trouver le sous-barème par ID
              $subBareme = SubBareme::findOrFail($id);

              $request->validate([
                  'name' => 'required|string|unique:base.sub_baremes,name,' . $subBareme->id,
                  'bareme_conduite_id' => 'required|exists:base.bareme_conduites,id',
                  'eliminatoire' => 'boolean',
              ]);

              // Créer un tableau de données à partir de la requête
              $data = $request->only(['name', 'bareme_conduite_id', 'eliminatoire']);

              // Mettre à jour le sous-barème
              $subBareme->update($data);

              return $this->successResponse($subBareme, "Sous-barème mis à jour avec succès.");
          } catch (\Throwable $e) {
              logger()->error($e);
              return $this->errorResponse("Erreur lors de la mise à jour du sous-barème.", [$e->getMessage()]);
          }
      }

      // Fonction pour récupérer les sous-barèmes par ID de barème
      public function getSubBaremesByBaremeId($baremeId)
      {
          try {
              $subBaremes = SubBareme::where('bareme_conduite_id', $baremeId)->get();
              return $this->successResponse($subBaremes, "Sous-barèmes récupérés avec succès.");
          } catch (\Exception $e) {
              return $this->errorResponse("Erreur lors de la récupération des sous-barèmes.", [$e->getMessage()]);
          }
      }
}
