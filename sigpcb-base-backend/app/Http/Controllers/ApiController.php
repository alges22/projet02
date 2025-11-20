<?php

namespace App\Http\Controllers;

use App\Services\Resp;
use Closure;
use Illuminate\Pagination\Paginator;
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
    protected function successResponse($data, $message = "Success", int $statuscode = Response::HTTP_OK, $responsecode = null)
    {
        return Resp::success($data, $message, $statuscode, $responsecode);
    }
    /**
     * Send server error response
     *
     * @param  $errors
     * @param mixed $data
     * @param string $message
     * @param integer|null $responsecode
     * @param int $statuscode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message = "Une erreur inattendue s'est produite", $errors = [],  $data = null,  $statuscode = Response::HTTP_INTERNAL_SERVER_ERROR, $responsecode = null)
    {
        return Resp::error($message, $errors, $data, $statuscode, $responsecode);
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

    public function sendValidatorErrors(\Illuminate\Validation\Validator $validator, string $message = "La validation a échoué", $data = null)
    {
        return $this->errorResponse($message, $validator->errors(), statuscode: 422, data: $data);
    }
}
