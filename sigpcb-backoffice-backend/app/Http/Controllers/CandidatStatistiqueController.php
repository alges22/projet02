<?php

namespace App\Http\Controllers;

use App\Services\Statistics\RapportActivites;
use App\Services\Statistics\RapportSyntethique;
use App\Services\Statistics\StatGlabal;
use Illuminate\Http\Request;

class CandidatStatistiqueController extends ApiController
{

    public function index(Request $request)
    {
        $this->hasAnyPermission(["all", "read-statistics"]);

        $type = $request->get('type');
        if ($type == 'activity') {
            $data = (new RapportActivites())->get($request);
        } elseif ($type == "global") {
            $data = (new StatGlabal())->get($request);
        } else {
            $data = (new RapportSyntethique())->get($request);
        }

        return $this->successResponse($data);
    }
}
