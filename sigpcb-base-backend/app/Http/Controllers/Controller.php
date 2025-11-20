<?php

namespace App\Http\Controllers;

use App\Models\Admin\Examen;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API Base",
 *      description="Gestion des API de base",
 *      @OA\Contact(
 *          email="contact@exemple.com",
 *          name="Support client"
 *      ),
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function examen(): Examen | null
    {
        $examen_id =  request('examen_id');
        $examen  = null;
        if ($examen_id) {
            $examen = Examen::find($examen_id);
        }
        return $examen;
    }
}
