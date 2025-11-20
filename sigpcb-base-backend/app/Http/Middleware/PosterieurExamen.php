<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Admin\Examen;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PosterieurExamen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->has('examen_id')) {
            return response()->json([
                "status" => false,
                "message" => "Aucun examen sélectionné",
                "errors" => ["examen_id" => "Aucun examen sélectionné"],
                "responsecode" => 404
            ]);
        }
        $examen = Examen::find($request->examen_id);

        if (!$examen) {
            return response()->json([
                "status" => false,
                "message" => "L'examen sélectionné n'existe pas",
                "errors" => ["examen_id" => "L'examen sélectionné n'existe pas"],
                "responsecode" => 404
            ]);
        } else {
            $dateCode = Carbon::parse($examen->date_code);

            # Si la date est antérieure à aujourd'hui
            if ($dateCode->isPast()) {
                return response()->json([
                    "status" => false,
                    "message" => "Vous ne pouvez pas assigner  d'inspecteur à un examen antérieur",
                    "errors" => ["examen_id" => "Vous ne pouvez pas assigner  d'inspecteur à un examen antérieur"],
                    "responsecode" => 404
                ]);
            }
        }


        return $next($request);
    }
}
