<?php

namespace App\Services\Imports\Validation;

use App\Models\User;
use App\Services\Api;
use App\Models\AnnexeAnatt;
use App\Models\Examinateur;
use Illuminate\Support\Str;
use App\Services\GetCandidat;
use Illuminate\Support\Carbon;
use App\Models\Base\Departement;
use App\Models\AutoEcole\Licence;
use App\Models\AutoEcole\Agrement;
use App\Models\AutoEcole\Vehicule;
use App\Models\AutoEcole\AutoEcole;
use App\Models\AutoEcole\Promoteur;
use App\Models\Base\CategoriePermis;
use Illuminate\Support\Facades\Auth;
use App\Services\Imports\FromExcelRow;
use App\Models\AutoEcole\DemandeAgrement;
use Illuminate\Support\Facades\Validator;
use App\Models\Examinateur\ExaminateurNewI;
use App\Models\ExaminateurCategoriePermis;
use App\Services\Imports\FromExaminateurRow;
use App\Services\Exception\ImportationException;

class ExaminateurEntryValidation
{
    private $categoriePermisIds = [];
    private $email = [];
    private $npi = [];
    private $data = [];

    public function validate(FromExaminateurRow $r)
    {
        $this->data = [
            "npi" => trim($r->getNpi() ?? ''),
            "email" => strtolower(trim($r->getEmail() ?? "")),
            "annexe" => trim($r->getAnnexe() ?? ""),
            "categorie_permis_ids" => trim($r->getCategoriesGerees() ?? ""),
            "num_permis" => trim($r->getNumeroPermis() ?? ""),
        ];

        $this->validateAnnexe();
        $this->validateCategories();
        $this->validateEmail();
        $this->validateNpi();

        return $this->data;
    }


    private function validateAnnexe()
    {
        $AnnexeName = strtoupper(trim($this->data['annexe']));

        $annexe = $this->findAnnexe($AnnexeName);

        if (!$annexe) {
            throw new ImportationException("L'annexe {$AnnexeName} n'existe pas ou elle est peut-être mal écrite");
        }
        $this->data['annexe'] = $annexe;

        return $annexe;
    }

    private function validateCategories()
    {
        $categories = array_map('trim', explode(',', $this->data['categorie_permis_ids']));
        // Nettoyer chaque élément du tableau en supprimant les espaces et les guillemets
        $categories = array_map(function ($item) {
            return trim($item, " \t\n\r\0\x0B\"");
        }, $categories);
        $categoryIds = [];
        foreach ($categories as $categori) {
            $categorie = $this->findCategorie($categori);
            if (!$categorie) {
                throw new ImportationException("La catégorie {$categori} n'existe pas ou est peut-être mal écrite");
            }
            $categoryIds[] = $categorie->id;
        }

        $this->data['categorie_permis_ids'] = $categoryIds;

        return $categoryIds;
    }

    private function validateEmail()
    {
        $validator = Validator::make($this->data, [
            "email" => "required|email"
        ]);
        if ($validator->fails()) {
            throw new ImportationException("L'email: {$this->data['email']} est invalide.");
        }
        $emailExists = User::where('email', $this->data['email'])->first();
        if ($emailExists) {
            $npi = $emailExists->npi;
            $id = $emailExists->id;
            $npiNew = trim($this->data['npi']);
            if($npi != $npiNew){
                throw new ImportationException("Ce email existe déjà en tant que agent ANaTT : '{$this->data['email']}' et le numéro npi envoyé n'est pas conforme a celui existant dans la base de données, veuillez corriger");
            }

            $examinateurExist = Examinateur::where('user_id', $id)->first();
            if ($examinateurExist) {
                throw new ImportationException("Ce npi existe déjà en tant que examinateur ANaTT : '{$this->data['npi']}'");
            }

        }

        return $this->data['email'];
    }

    private function validateNpi()
    {
        $responseP = Api::base('GET', "candidats/" . $this->data['npi']);
        // Vérifier la réponse de l'API externe
        if (!$responseP->successful()) {
            throw new ImportationException("Le numéro NPI de l'examinateur n'existe pas chez l'ANIP.");
        }

        $npiExists = User::where('npi', trim($this->data['npi']))->first();
        if ($npiExists) {
            $id = $npiExists->id;

            $email = $npiExists->email;
            $emailNew = trim($this->data['email']);
            if($email != $emailNew){
                throw new ImportationException("Ce npi existe déjà en tant que agent ANaTT : '{$this->data['npi']}' et l'adresse email envoyé n'est pas conforme a celle existante dans la base de données, veuillez corriger");
            }

            $examinateurExist = Examinateur::where('user_id', $id)->first();
            if ($examinateurExist) {
                throw new ImportationException("Ce npi existe déjà en tant que examinateur ANaTT : '{$this->data['npi']}'");
            }
        }

        $npiMetierExists = ExaminateurNewI::where('npi', $this->data['npi'])->first();
        if ($npiMetierExists) {
            throw new ImportationException("Ce npi existe déjà en tant que examinateur sur metier : '{$this->data['npi']}'");
        }

        return $this->data['npi'];
    }


    private function findAnnexe($name)
    {
        $annexe =  AnnexeAnatt::where('name', 'like', "%$name%");

        if ($annexe->count() > 1 || $annexe->count() == 0) {
            return AnnexeAnatt::all()->filter(function ($c) use ($name) {
                return Str::slug($c->name) == Str::slug($name);
            })->first();
        }

        return $annexe->first();

    }


    private function findCategorie($name)
    {
        $cat = CategoriePermis::where('name', 'like', "%$name%");

        if ($cat->count() > 1 || $cat->count() == 0) {
            return CategoriePermis::all()->filter(function ($c) use ($name) {
                return Str::slug($c->name) == Str::slug($name);
            })->first();
        }

        return $cat->first();

    }



    private function generateRandomPassword()
    {
        // Définir les critères de sécurité du mot de passe
        $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

        do {
            // Générer un mot de passe aléatoire
            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@$!%*?&'), 0, 8);
        } while (!preg_match($regex, $password));

        return $password;
    }
    private function createCarbonDate($date_str): Carbon
    {
        if (is_numeric($date_str)) {

            return Carbon::createFromDate(1900, 1, 1)->addDays($date_str - 2);
        }
        // Remplace toutes les occurrences de "/" par "-"
        $date_str = str_replace('/', '-', $date_str);
        $carbonDate = \Carbon\Carbon::createFromFormat('d-m-Y', $date_str);

        return $carbonDate;
    }
}
