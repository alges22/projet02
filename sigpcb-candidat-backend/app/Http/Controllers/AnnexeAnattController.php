<?php

namespace App\Http\Controllers;
use App\Models\Admin\AnnexeAnatt;
use Illuminate\Http\Request;

class AnnexeAnattController extends ApiController
{
    public function index(Request $request)
    {
        try {
            $query = AnnexeAnatt::query()->with('annexeAnattDepartements');

            // Recherche
            if ($request->has('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('name ILIKE ?', ['%' . $searchTerm . '%']);
                });
            }

            if (request('liste') == 'paginate') {
                $annexe_anatts = $query->orderByDesc('id')->paginate(10);
            } else {
                $annexe_anatts = $query->orderByDesc('id')->get();
            }

            if ($annexe_anatts->isEmpty()) {
                return $this->successResponse([], "Aucun résultat trouvé", 200);
            }

            return $this->successResponse($annexe_anatts);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
        }
    }

    public function show($id)
    {
        try {
            $annexe = AnnexeAnatt::with('annexeAnattDepartements')->find($id);
            if (!$annexe) {
                return $this->errorResponse('Annexe introuvable');
            }
            return $this->successResponse($annexe);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }

}
