<?php

namespace App\Http\Controllers;

use App\Models\Agrement;
use Illuminate\Http\Request;

class AgrementController extends ApiController
{
    public function index()
    {

        $agrements = Agrement::all();

        return $this->successResponse($agrements);
    }
}
