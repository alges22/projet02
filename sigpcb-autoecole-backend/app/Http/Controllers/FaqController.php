<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Faq;

class FaqController extends ApiController
{
    public function index()
    {
        try {
            $faqs = Faq::where('type', 'autoecole')->orderByDesc('created_at')->get();
            return $this->successResponse($faqs);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}