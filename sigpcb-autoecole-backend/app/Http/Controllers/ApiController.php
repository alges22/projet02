<?php

namespace App\Http\Controllers;

use Closure;
use App\Services\Api;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pagination\LengthAwarePaginator;

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
     * Cette fonction nous sera utilise pour générer les paginations avec les maps
     *
     * @param LengthAwarePaginator $paginator
     * @param Closure $callable
     * @param array $merge au cas ou il y aurait d'autres données à ajouter
     */
    protected function withPagination(LengthAwarePaginator $paginator, Closure $closure, array $merge = [], $message = "Succès")
    {

        # On crée une variable pour la pagination
        $dataPaginate["paginate_data"]['data'] = $paginator->map($closure)->all();

        # Ajout de la page courante
        $dataPaginate['paginate_data']['current_page'] = $paginator->currentPage();

        # Ajout de l'url précédente
        $dataPaginate['paginate_data']['previous_url'] = $paginator->previousPageUrl();

        # Ajout de l'url suivante
        $dataPaginate['paginate_data']['next_url'] = $paginator->nextPageUrl();
        $dataPaginate['paginate_data']['per_page'] = $paginator->perPage();
        $dataPaginate['paginate_data']['total'] = $paginator->total();

        return $this->successResponse(array_merge($dataPaginate, $merge), $message);
    }
}
