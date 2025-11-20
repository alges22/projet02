<?php

namespace App\Http\Controllers;

use App\Models\Mention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MentionController extends ApiController
{
    public function index()
    {
        try {
            // Récupérer toutes les mentions
            $mentions = Mention::all();

            // Vérifier si des mentions ont été trouvées
            if ($mentions->isEmpty()) {
                return $this->successResponse([],'Aucune mention trouvée',200);
            }

            // Retourner la liste des mentions avec une réponse de succès
            return $this->successResponse($mentions, 'Liste des mentions récupérée avec succès');
        } catch (\Throwable $e) {
            // Gérer les erreurs imprévues
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des mentions', null, null, 500);
        }
    }

    public function show($id)
    {
        try {
            // Recherche de la mention par son ID
            $mention = Mention::find($id);

            // Vérifier si la mention existe
            if (!$mention) {
                return $this->errorResponse('Mention non trouvée', null, null, 404);
            }

            // Retourner la mention avec une réponse de succès
            return $this->successResponse($mention, 'Mention récupérée avec succès');
        } catch (\Throwable $e) {
            // Gérer les erreurs imprévues
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération de la mention', null, null, 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Valider les données entrantes
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'image' => 'required|image',
                'point' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), 422);
            }

            $name = $request->input('name');
            $point = $request->input('point');
            // Vérifier si un fichier image a été téléchargé
            if (!$request->hasFile('image')) {
                return $this->errorResponse("L'image est requise.", null, 422);
            }

            $imagePath = null;

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('photo', 'public');
            }

            // Créer une nouvelle mention
            $mention = Mention::create([
                'name' => $name,
                'image' => $imagePath,
                'point' => $point,
            ]);

            // Retourner la nouvelle mention avec une réponse de succès
            return $this->successResponse($mention, 'Mention créée avec succès', 201);
        } catch (\Throwable $e) {
            // Gérer les erreurs imprévues
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la création de la mention', null, null, 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            // Valider les données entrantes
            $validator = Validator::make($request->all(), [
                'name' => 'string',
                'image' => 'image',
                'point' => 'integer',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), 422);
            }

            // Récupérer la mention existante
            $mention = Mention::find($id);

            if (!$mention) {
                return $this->errorResponse("Mention introuvable.", null, 404);
            }

            // Mettre à jour les champs si les données sont fournies
            if ($request->has('name')) {
                $mention->name = $request->input('name');
            }
            if ($request->has('point')) {
                $mention->point = $request->input('point');
            }

            // Vérifier si une nouvelle image a été téléchargée et si elle est différente de l'ancienne
            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')->store('photo', 'public');
                if ($mention->image) {
                    // Supprimer l'ancienne image seulement si une nouvelle image est fournie
                    Storage::disk('public')->delete($mention->image);
                }
                $mention->image = $newImagePath;
            }

            // Enregistrer les modifications
            $mention->save();

            // Retourner la mention mise à jour avec une réponse de succès
            return $this->successResponse($mention, 'Mention mise à jour avec succès');
        } catch (\Throwable $e) {
            // Gérer les erreurs imprévues
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour de la mention', null, null, 500);
        }
    }



    public function destroy($id)
    {
        try {
            // Recherche de la mention par son ID
            $mention = Mention::find($id);

            // Vérifier si la mention existe
            if (!$mention) {
                return $this->errorResponse('Mention non trouvée', null, null, 404);
            }

            // Supprimer la mention
            $mention->delete();

            // Retourner une réponse de succès
            return $this->successResponse(null, 'Mention supprimée avec succès');
        } catch (\Throwable $e) {
            // Gérer les erreurs imprévues
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la suppression de la mention', null, null, 500);
        }
    }
}
