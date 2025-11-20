<?php

namespace App\Services;

use App\Models\AnipUser as User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AnipRequestProcess
{
    const CACHE_EXPIRE_HOURS = 24;

    /**
     *
     * @var Collection<int, User>
     */
    private $collection;

    /**
     * @var Collection
     */
    private $toUpdate;

    public function  __construct(private $npis)
    {
        $this->collection = collect();
        $this->toUpdate = collect();
    }

    private function existInCache(string $npi): User | null
    {
        /** @var User */
        $user = User::where('npi', $npi)->first();
        if (!$user) {
            return null;
        }

        if (!App::isLocal()) {
            if (now()->isAfter($user->last_updated->copy()->addHours(static::CACHE_EXPIRE_HOURS))) {
                return null;
            }
        }
        return $user;
    }

    /**
     * Préparation de la mise en cache
     */
    private function prepare()
    {
        foreach ($this->npis as $npi) {
            $user = $this->existInCache($npi);
            if (!$user) {
                $this->toUpdate->push($npi);
            } else {
                $this->collection =  $this->collection->push($user);
            }
        }
    }

    /**
     * Mettre à jour les NPIS qui ne sont pas chez nous ou dont la validité du cache est expiré
     *
     */
    private function update()
    {
        $users = [];
        $startTime = microtime(true);
        # On récupère juste  les informations depuis l'ANIP sans la mise en cache
        # Pour accéler un peu la requête
        foreach ($this->toUpdate as $npi) {
            $user = $this->existInCache($npi);
            if ($user) {
                continue;
            }

            $endTime = microtime(true);

            # Dès que la requête dépasse 50s, on attend et on fait 12s d'abord avant de continuer
            if (($endTime - $startTime) > 50) {
                sleep(12);
                $startTime = microtime(true);
            }

            # Mise en cache
            $u = User::whereNpi($npi)->first();
            if (!$u) {
                $u =   User::create($this->fetch($npi));
            } else {
                $u->update($this->fetch($npi));
            }
            $users[] = $u;
        }

        ## On crée les candidats, et puis on ajoute à la liste des candidats
        $this->collection = $this->collection->merge($users)->values();
    }

    /**
     * Recupère le candidat depuis l'ANIP
     *
     * @param string $npi
     * @return array
     */
    private function fetch(string $npi)
    {
        try {
            $content = str_replace("{npi}", $npi, file_get_contents(base_path("soap.xml")));
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

            $data['last_updated'] = now();

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

    public function getCollection(): Collection
    {
        $this->prepare();
        $this->update();
        return $this->collection;
    }

    private function extract(string $xml, $npi)
    {
        $data = [];
        preg_match('/<NPI>(.*?)<\/NPI>/', $xml, $matches);
        if (count($matches) > 1) {
            $data['npi'] = $matches[1];
        }

        preg_match('/<LASTNAME>(.*?)<\/LASTNAME>/', $xml, $matches);
        if (count($matches) > 1) {
            $data['nom'] =  $matches[1] ?? "Sans nom";
        }

        preg_match('/<FIRSTNAME>(.*?)<\/FIRSTNAME>/', $xml, $matches);
        if (count($matches) > 1) {
            $data['prenoms'] =  $matches[1] ?? "Sans Prénoms";
        }

        preg_match('/<BIRTHDATE>(.*?)<\/BIRTHDATE>/', $xml, $matches);
        if (count($matches) > 1) {
            $data['date_de_naissance'] =  $matches[1];
        }

        preg_match('/<BIRTH_PLACE>(.*?)<\/BIRTH_PLACE>/', $xml, $matches);
        if (count($matches) > 1) {
            $data['lieu_de_naissance'] =  $matches[1];
        }

        preg_match('/<SEXE>(.*?)<\/SEXE>/', $xml, $matches);
        if (count($matches) > 1) {
            $data['sexe'] =  $matches[1][0] ?? "-";
        }

        preg_match('/<PHONE_NUMBER>(.*?)<\/PHONE_NUMBER>/', $xml, $matches);
        if (count($matches) > 1) {
            $phone = $matches[1];
            if (strlen($phone) < 4) {
                throw new GlobalException("Le numéro de téléphone associé à ce numéro NPI ne semble pas être correcte", 1);
            }
            $data['telephone'] = $phone;
        }

        preg_match('/<PHONE_NUMBER_INDICATIF>(.*?)<\/PHONE_NUMBER_INDICATIF>/', $xml, $matches);
        if (count($matches) > 1) {
            $data['telephone_prefix'] = str($matches[1] ?? "-")->after("+")->toString();
        }

        preg_match('/<RESIDENCE_ADDRESS>(.*?)<\/RESIDENCE_ADDRESS>/', $xml, $matches);
        if (count($matches) > 1) {
            $data['adresse'] =  $matches[1] ?? "-";
        }

        preg_match('/<RESIDENCE_TOWN>(.*?)<\/RESIDENCE_TOWN>/', $xml, $matches);
        if (count($matches) > 1) {
            $data['ville_residence'] =  $matches[1] ?? "-";
        }

        preg_match('/<NATIONALITY>(.*?)<\/NATIONALITY>/', $xml, $matches);
        if (count($matches) > 1) {
            $data['nationality'] = $matches[1] ?? "Sans nationalité";
        }

        preg_match('/<EMAIL>(.*?)<\/EMAIL>/', $xml, $matches);
        if (count($matches) > 1) {
            $validator = Validator::make([
                'email' => $email = $matches[1]
            ], [
                'email' => "email"
            ]);

            if (!$validator->fails()) {
                $data['email'] = $email;
            } else {
                $data['email'] = null;
            }
        }

        if (count($data) < 2) {
            throw new GlobalException("Le numéro NPI: $npi ne semble pas être correcte", 1);
        }

        $data['avatar'] = "photos/avatar.png";
        $data['signature'] = null;
        return $data;
    }
}
