<?php

namespace App\Services;

use App\Models\Service;
use App\Models\DemandeAgrement;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function loadService($name, $id)
    {
        $method = match ($name) {
            Service::DEMANDE_AGREMENT => 'demandeAgrement',
            default => null
        };

        if (!$method) {
            throw ValidationException::withMessages([
                'service' => "Service not found",
            ]);
        }
        return $this->{$method}($id);
    }

    /**
     * Les données pour la création de paiement
     * @param mixed $id
     * @return array
     */
    public function demandeAgrement($id): array
    {
        $demande =  DemandeAgrement::find($id);

        if (!$demande) {
            throw ValidationException::withMessages([
                "id" => "Service not found",
            ]);
        }

        return [
            "amount" => 1500,
            "model" => $demande,
            "payer" => [
                "npi" => $demande->promoteur_npi,
            ],
            "callback_url" => env('FRONTEND_URL') . "/demande-agrement"
        ];
    }
}
