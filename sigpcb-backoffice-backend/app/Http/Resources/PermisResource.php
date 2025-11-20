<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermisResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "candidat" => $this->candidat,
            "categorie_permis" => $this->categoriePermis,
            "npi" => $this->npi,
            "delivered_at" => $this->delivered_at,
            "expired_at" => $this->expired_at,
            "code" => $this->code_permis
        ];
    }
}
