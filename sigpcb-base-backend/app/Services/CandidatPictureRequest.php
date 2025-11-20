<?php
namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CandidatPictureRequest
{
    protected $npis;

    // Le constructeur prend une liste de NPI (ou un seul)
    public function __construct(array $npis)
    {
        $this->npis = $npis;
    }

    /**
     * Récupère les informations des photos pour un ou plusieurs NPI
     *
     * @return Collection
     */
    public function getCollection(): Collection
    {
        $photos = collect();

        foreach ($this->npis as $npi) {
            // Récupération des données de la photo pour chaque NPI
            try {
                $data = $this->fetch($npi);
                // On ajoute l'image à la collection
                $photos->push($data);
            } catch (\Exception $e) {
                Log::error("Erreur lors de la récupération de la photo pour NPI: $npi", ['error' => $e->getMessage()]);
            }
        }

        return $photos;
    }

    /**
     * Récupère les données de l'image pour un NPI spécifique
     *
     * @param string $npi
     * @return array
     */
    public function fetch(string $npi): array
    {
        try {
            $content = str_replace("{npi}", $npi, file_get_contents(base_path("soap-picture.xml")));
            $query = Http::withOptions([
                "verify" => false,
            ])->withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => '',
            ])->send("POST", 'https://common-ss.xroad.bj:8443', [
                "body" => $content,
            ]);

            # Au cas ou une erreur sur le server est survenu
            if (!$query->successful()) {
                if ($query->status() == 404) {
                    throw new  GlobalException("Le numéro NPI: $npi est introuvable", 1);
                }
                # Empêche la suite
                $query->toException();
            }

            $data = $this->extract($query->body(), $npi);
            return $data;
        } catch (\Throwable $th) {
            logger()->error($th);
            if ($th instanceof GlobalException) {
                throw new GlobalException($th->getMessage(), 1);
            } else {
                throw new GlobalException($th->getMessage(), 1);
            }
        }
    }

    /**
     * Extrait les données de la réponse XML
     *
     * @param string $xml
     * @param string $npi
     * @return array
     */
    private function extract(string $xml, string $npi): array
    {
        $data = [];

        preg_match('/<id_photo>(.*?)<\/id_photo>/s', $xml, $matches);
        if (count($matches) >= 1) {
            $data['image'] = $matches[1];
        } else {
            $data['image'] = "Sans image";
        }

        if (empty($data)) {
            throw new \Exception("Le numéro NPI: $npi ne semble pas être correct ou ne possède pas de photo");
        }

        return $data;
    }
}
