<?php

namespace App\Http\Controllers;

use App\Models\Admin\Faq;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class FaqController extends ApiController
{
    public function index()
    {
        try {
            $faqs = Faq::where('type', 'candidat')->orderByDesc('created_at')->get();
            return $this->successResponse($faqs);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
