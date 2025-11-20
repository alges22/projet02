<?php

namespace App\Http\Controllers\Resultats;

use App\Http\Controllers\Resultats\ResultatCodeController;

class StatistiqueController extends ResultatCodeController
{

    public function codes()
    {
        $stats = [
            "presentes" => $this->countWheres([]),
            "admis" => $this->countWheres(['resultat_code' => 'success']),
            "recales" => $this->countWheres(['resultat_code' => 'failed']),
            "abscents" => $this->countWheres(['presence' => 'abscent']),
        ];
        return $this->successResponse($stats);
    }

    public function conduites()
    {
        $this->instance = $this->instance->where('resultat_code', 'success');

        $stats = [
            "admis" => $this->instance->where('resultat_conduite', 'success')->count(),
            "recales" => $this->instance->where('resultat_conduite', 'failed')->count(),
            "abscents" => $this->instance->where('presence_conduite', 'abscent')->count(),
            "presentes" => $this->instance->count()
        ];
        return $this->successResponse($stats);
    }

    private function countWheres(array $filters, ?string $nullables = null): int
    {
        $columns = array_keys($filters);
        $columns[] = 'id';
        $collections = $this->instance->get($columns);

        $filters = array_map(function ($f) use ($nullables) {
            $values[] = $f;
            if ($nullables) {
                $values[] = null;
            }
            return $values;
        }, $filters);

        foreach ($filters as $key => $values) {
            $collections = $collections->whereIn($key, $values);
        }
        return $collections->count();
    }
}
