<?php

namespace App\Http\Controllers;

use App\Services\Api;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{

    /**
     * Send success response
     *
     * @param object|array|integer|string|bool $data
     * @param string $message
     * @param integer|null $responsecode
     * @param int $statuscode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, $message = "Success", int $statuscode = Response::HTTP_OK)
    {
        return response()->json(
            [
                "status" => true,
                "message" => $message,
                "data" => $data,
                "statuscode" => $statuscode
            ]
        )->setStatusCode($statuscode);
    }
    /**
     * Send server error response
     *
     * @param mixed $data
     * @param string $message
     * @param integer|null $responsecode
     * @param int $statuscode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message = "Error", $errors = [],  $data = null,  $statuscode = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $responsesData =  [
            "status" => false,
            "message" => $message,
            "errors" => $errors,
        ];

        if ($data) {
            $responsesData['data'] = $data;
        }
        return response()->json($responsesData)->setStatusCode($statuscode);
    }

    /**
     * Format the success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponseclient($data, $message = 'Success', $statusCode = 200)
    {
        $responseData = $data['data']; // Récupérer seulement les données de la réponse

        $response = [
            'status' => true,
            'message' => $message,
            'data' => $responseData,
        ];

        return response()->json($response, $statusCode);
    }

    /**
     * Send server error response
     *
     * @param array $errors
     * @param mixed $data
     * @param string $message
     * @param integer|null $responsecode
     * @param int $statuscode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponseclient($message = "Error", $errors = [], $data = null, $statusCode = 500)
    {
        $responseData = [
            "status" => false,
            "message" => $message,
            "errors" => $errors,
            "data" => $data,
        ];

        return response()->json($responseData, $statusCode);
    }

    protected  function exprotFromBase(string $url, array $params = [])
    {
        # Appel Base
        $response = Api::base('GET', $url, $params);
        # Retrait des informations d'entete
        $message = $response->json("message", "Une erreur est survenue ");
        $data = $response->json('data', null);
        $errors = $response->json('errors', null);
        $statuscode = $response->status();

        # S'il y a une erreur on retourne l'erreur telle quell
        if (!$response->successful()) {
            return $this->errorResponse($message, $errors, $data, $statuscode);
        }

        # On recupère la bonne information
        $data = Api::data($response);

        return $this->successResponse($data, $message, $statuscode);
    }

    protected function exportFromBase(string $url, array $params = [])
    {
        return $this->exprotFromBase($url, $params);
    }
    protected  function importFromBase(string $url, array $params = [])
    {
        # Appel Base
        $response = Api::base('GET', $url, $params);
        # On recupère la bonne information
        $data = Api::data($response);

        return $data;
    }
    /**
     * Post des choses sur base
     *
     * @param string $url
     * @param array $params
     */
    protected function postToBase(string $url, array $params = [])
    {
        # Appel Base
        $response = Api::base('POST', $url, $params);
        # Retrait des informations d'entete
        $message = $response->json("message", "Une erreur est survenue ");
        $data = $response->json('data', null);
        $errors = $response->json('errors', null);
        $statuscode = $response->status();

        # S'il y a une erreur on retourne l'erreur telle quell
        if (!$response->successful()) {
            return $this->errorResponse($message, $errors, $data, $statuscode);
        }

        # On recupère la bonne information
        $data = Api::data($response);

        return $this->successResponse($data, $message, $statuscode);
    }

    public function sendValidatorErrors(\Illuminate\Validation\Validator $validator, string $message = "La validation a échoué", $data = null)
    {
        return $this->errorResponse($message, $validator->errors(), statuscode: 422, data: $data);
    }
}
