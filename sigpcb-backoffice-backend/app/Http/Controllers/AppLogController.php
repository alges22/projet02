<?php

namespace App\Http\Controllers;

use App\Models\AppLog;
use App\Models\User;
use Illuminate\Http\Request;

class AppLogController extends ApiController
{
    public function index()
    {
        $this->hasAnyPermission(["all","read-log"]);

        try {
            $logs = AppLog::orderBy('id', 'desc')->paginate(10);

            // Parcourir les logs pour récupérer les informations sur l'utilisateur si l'ID est différent de 0
            foreach ($logs as $log) {
                if ($log->user_id !== 0) {
                    // Récupérer l'utilisateur correspondant à l'ID
                    $user = User::find($log->user_id);
                    // Si l'utilisateur existe, ajouter ses informations au log
                    if ($user) {
                        $log->user = $user;
                    }
                }
            }

            return $this->successResponse($logs);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des logs.', [], 500);
        }
    }


}
