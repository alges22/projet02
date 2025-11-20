<?php

namespace App\Http\Controllers;


class ExamenController extends ApiController
{
    /**
     * Récupère la liste des examnateurs
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $params = request()->all(); //les paramètres de filtres
        return $this->exprotFromBase("examens", $params);
    }
}
