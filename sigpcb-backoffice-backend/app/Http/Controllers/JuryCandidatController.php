<?php

namespace App\Http\Controllers;

use App\Services\Api;
use Illuminate\Http\Request;

class JuryCandidatController extends ApiController
{
    public function show($id)
    {
            try {
                    $path = "jury/" . $id;
                    $response =  Api::base('GET', $path);

                    if ($response->ok()) {
                    $responseData = $response->json();
                    return $this->successResponseclient($responseData, 'Success', 200);
                    } else {
                    $errorData = $response->json();
                    $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la récupération.';
                    $errors = $errorData['errors'] ?? null;
                    return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
                    }
            } catch (\Throwable $th) {
                    logger()->error($th);
                    return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
            }
    }
}
