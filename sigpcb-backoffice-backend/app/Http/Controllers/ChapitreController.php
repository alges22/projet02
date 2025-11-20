<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;

use App\Services\Api;
use Illuminate\Http\Request;

class ChapitreController extends ApiController
{
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-chapitres-management","read-chapitres-management"]);
        try {

            $path = "chapitres";
            $response =  Api::base('GET', $path, $request->all());

            $data = Api::data($response);

            return $this->successResponse($data);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue.', statuscode: 500);
        }
    }
    public function getMany()
    {
        $this->hasAnyPermission(["all", "edit-chapitres-management","read-chapitres-management"]);
        try {
            $chapitreIds = explode(',', request('ids', ''));
            $chapitres = [];
            foreach ($chapitreIds as $chapitreId) {
                $chapitreId = trim($chapitreId);
                $path = "chapitres/" . $chapitreId;
                $response =  Api::base('GET', $path);

                $data = Api::data($response);
                $chapitres[] = $data;
            }
            return $this->successResponse($chapitres);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue', statuscode: 500);
        }
    }

    public function store(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-chapitres-management"]);
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'description' => 'nullable',
                    'categorie_permis_ids' => 'required|array|min:1',
                    'categorie_permis_ids.*' => 'required'
                ],
                [
                    'name.required' => 'Le champ name est requis.',
                    'name.unique' => 'Ce nom de chapitre existe déjà.',
                    'categorie_permis_ids.required' => 'Vous devez sélectionner au moins une catégorie de permis.',
                    'categorie_permis_ids.min' => 'Vous devez sélectionner au moins une catégorie de permis.',
                    'categorie_permis_ids.*.required' => 'Vous devez sélectionner au moins une catégorie de permis.',

                ]
            );

            if ($validator->fails()) {
                return $this->errorResponseclient("La validation a échoué.", $validator->errors(), null, 422);
            }

            $data = [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'categorie_permis_ids' => $request->input('categorie_permis_ids'),
            ];

            // Effectuer la requête POST à l'API en utilisant votre méthode d'assistance
            $response = Api::base('POST', 'chapitres', $data);

            // Vérifier la réponse de l'API externe
            if ($response->ok()) {
                $responseData = $response->json();
                return $this->successResponseclient($responseData, 'Chapitre créé avec succès.', 200);
            } else {
                $errorData = $response->json();
                $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la création du Chapitre.';
                $errors = $errorData['errors'] ?? null;
                return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
    public function show($id)
    {
        $this->hasAnyPermission(["all", "edit-chapitres-management","read-chapitres-management"]);
        try {
            $path = "chapitres/" . $id;
            $response =  Api::base('GET', $path);

            if ($response->ok()) {
                $responseData = $response->json();
                return $this->successResponseclient($responseData, 'Success', 200);
            } else {
                $errorData = $response->json();
                $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la récupération';
                $errors = $errorData['errors'] ?? null;
                return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    public function update(Request $request, $id)
    {
        $this->hasAnyPermission(["all", "edit-chapitres-management"]);
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'description' => 'nullable',
                    'categorie_permis_ids' => 'required|array|min:1',
                    'categorie_permis_ids.*' => 'required'
                ],
                [
                    'name.required' => 'Le champ name est requis.',
                    'name.unique' => 'Ce nom de chapitre existe déjà.',
                    'categorie_permis_ids.required' => 'Vous devez sélectionner au moins une catégorie de permis.',
                    'categorie_permis_ids.min' => 'Vous devez sélectionner au moins une catégorie de permis.',

                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), 422);
            }

            // Préparer les données à envoyer à l'API externe
            $data = [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'categorie_permis_ids' => $request->input('categorie_permis_ids'),
            ];

            // Effectuer la requête PUT à l'API externe
            $response = Api::base('PUT', 'chapitres/' . $id, $data);

            // Vérifier la réponse de l'API externe
            if ($response->successful()) {
                // Obtenir les données de la réponse
                $responseData = $response->json();

                // Retourner une réponse de succès avec les données
                return $this->successResponseclient($responseData, 'chapitre mis à jour avec succès.', 200);
            } else {
                // Retourner une réponse d'erreur si la requête a échoué
                $errorData = $response->json();
                $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la mise à jour.';
                $errors = $errorData['errors'] ?? null;
                return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    public function destroy($id)
    {
        $this->hasAnyPermission(["all", "edit-chapitres-management"]);
        try {
            $response = Api::base('DELETE', 'chapitres/' . $id);

            if ($response->successful()) {
                $responseData = $response->json();
                return $this->successResponseclient($responseData, 'Chapitre supprimé avec succès.', 200);
            } else {
                $errorData = $response->json();
                $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la suppression';
                $errors = $errorData['errors'] ?? null;
                return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
