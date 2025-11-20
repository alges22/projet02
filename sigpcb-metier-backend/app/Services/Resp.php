<?php

namespace App\Services;

use App\Models\Entreprise;
use Symfony\Component\HttpFoundation\Response;

class Resp
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
    public static function success($data, $message = "Success", int $statuscode = Response::HTTP_OK, $responsecode = null)
    {
        return response()->json(
            [
                "status" => true,
                "message" => $message,
                "data" => $data,
                "responsecode" => $responsecode
            ]
        )->setStatusCode($statuscode);
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
    public static function error(string $message = "Error", $errors = [],  $data = null,  $statuscode = Response::HTTP_INTERNAL_SERVER_ERROR, $responsecode = null)
    {
        $responsesData =  [
            "status" => false,
            "message" => $message,
            "errors" => $errors,
            "responsecode" => $responsecode
        ];

        if ($data) {
            $responsesData['data'] = $data;
        }
        return response()->json($responsesData)->setStatusCode($statuscode);
    }

 
}