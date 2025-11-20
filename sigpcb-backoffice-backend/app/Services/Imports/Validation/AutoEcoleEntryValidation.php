<?php

namespace App\Services\Imports\Validation;

use App\Services\Api;
use App\Services\Dgi;
use Illuminate\Support\Str;
use App\Services\GetCandidat;
use Illuminate\Support\Carbon;
use App\Models\Base\Departement;
use App\Models\AutoEcole\Licence;
use App\Models\AutoEcole\Agrement;
use App\Models\AutoEcole\Vehicule;
use App\Models\AutoEcole\AutoEcole;
use App\Models\AutoEcole\Promoteur;
use App\Services\Imports\FromExcelRow;
use App\Models\AutoEcole\DemandeAgrement;
use App\Models\Moniteur as AnattMoniteur;
use Illuminate\Support\Facades\Validator;
use App\Services\Exception\ImportationException;

class AutoEcoleEntryValidation
{
    private $vehicules = [];
    private $names = [];
    private $licences = [];
    private  $agrements = [];
    private $emailPro = [];
    private $npiEmailIfuMap = [];
    private $data = [];

    public function validate(FromExcelRow $r)
    {
        $name = trim($r->getAutoEcole());
        $this->data = [
            "name" => $name,
            "npi" => trim($r->getNpiDuPromoteur() ?? ''),
            "licence" => trim($r->getCodeLicence() ?? ''),
            "agrement" => trim($r->getReferenceAutorisation() ?? ''),
            "adresse" => trim($r->getAdresseQuartierilotparcelle() ?? ""),
            "phone" => trim($r->getTelephoneProfessionnel() ?? ""),
            "email" => strtolower(trim($r->getEMailProfessionnel() ?? "")),
            "ifu" => trim($r->getIfu() ?? ''),
            "immatriculations" => trim($r->getVehiculesDeLautoEcole() ?? ""),
            "slug" => Str::slug($name),
            "departement" => trim($r->getDepartement() ?? ""),
            "commune" => trim($r->getCommune() ?? ""),
            "moniteurs_npis" => trim($r->getNpisDesMoniteursSeparesPar() ?? ""),
            "emailPromoteur" => trim($r->getEMailDuPromoteur() ?? ""),
            "data_licence" => trim($r->getDateExpirationDeLaLicence() ?? ""),

        ];

        $this->validateAutoEcole();
        $this->validateCommune();
        $this->validateNpiPromoteur();
        $this->validateLicence();
        $this->validateEmailProfesionnel();
        $this->validateAdresse();
        $this->validatePhone();
        $this->validateNpi();
        $this->validateEmailPromoteur();
        $this->validateIfu();
        $this->validatePromoteur();
        $this->validateImmatriculations();
        $this->validateAgrement();
        $this->validateMoniteurs();
        $this->validateDate();

        return $this->data;
    }

    private function validateMoniteurs()
    {
        $moniteurs = array_map('trim', explode(',', $this->data['moniteurs_npis']));
        $moniteurs = array_map(function ($item) {
            return trim($item);
        }, $moniteurs);
        $moniteurs = array_filter($moniteurs);
        if (count($moniteurs) < 2) {
            throw new ImportationException("Les moniteurs de l'auto-école  {$this->data['name']} n'atteignent pas 2 ou peut-être leurs NPIs sont mal renseignés",);
        }
        foreach ($moniteurs as $key => $moniteurNpi) {
            try {
                GetCandidat::findOne($moniteurNpi);
                $existing = AnattMoniteur::whereNpi($moniteurNpi)->first();
                if (!$existing) {
                    AnattMoniteur::create([
                        "npi" => $moniteurNpi,
                        "agent_id" => auth()->id(),
                        "date_validation" => now(),
                    ]);
                }
            } catch (\Throwable $th) {
                throw new ImportationException("Le NPI du moniteur: {$moniteurNpi} n'existe pas chez l'ANIP.",);
            }
        }

        $this->data['moniteurs'] = $moniteurs;
    }


    private function validateCommune()
    {
        $communeName = $this->data['commune'];
        $departementName = $this->data['departement'];
        $departement = Departement::all()->filter(function ($c) use ($departementName) {
            return Str::slug($c->name) == Str::slug($departementName);
        })->first();

        if (!$departement) {
            throw new ImportationException("Le département {$departementName} n'existe pas pour l'auto-école {$this->data['name']} ou il est peut-être mal écrit.");
        }

        $commune = $departement->communes->filter(function ($c) use ($communeName) {
            return Str::slug($c->name) == Str::slug($communeName, dictionary: ["'" => ""]);
        })->first();
        if (!$commune) {
            throw new ImportationException("La commune {$communeName} de l'auto-école {$this->data['name']} elle est peut-être mal écrite, ou n'est peut-être pas dans le département: {$departementName}.");
        }
        $this->data['commune'] = $commune;
        $this->data['departement'] = $departement;
    }


    private function validateAutoEcole()
    {
        $validator = Validator::make($this->data, [
            "name" => "required|min:3"
        ]);
        if ($validator->fails()) {
            throw new ImportationException("Vérifiez tous les noms des auto-écoles, un nom semble être vide ou trop court.");
        }

        if (in_array($this->data['slug'], $this->names)) {
            throw new ImportationException("Le nom '{$this->data['name']}' apparait plusieurs fois, vérifiez la casse, et les espaces par exemple.");
        } else {
            $this->names[] = $this->data['slug'];
        }

        $aenameExists = AutoEcole::where('slug', $this->data['slug'])->first();
        if ($aenameExists) {
            throw new ImportationException("Le nom d'auto école '{$this->data['name']}' est déjà pris.");
        }

        $aenameExists = AutoEcole::where('name', $this->data['name'])->first();
        if ($aenameExists) {
            throw new ImportationException("Le nom d'auto école '{$this->data['name']}' est déjà pris.");
        }
        $aenameExists = DemandeAgrement::where('auto_ecole', $this->data['name'])->first();
        if ($aenameExists) {
            throw new ImportationException("Le nom d'auto école '{$this->data['name']}' est déjà pris (indice: demande d'agrément).");
        }
    }

    private function validateLicence()
    {
        # Vérification du code de licence
        $validator = Validator::make($this->data, [
            "licence" => "required|min:3"
        ]);
        if ($validator->fails()) {
            throw new ImportationException("Le code licence de l'auto-école: {$this->data['licence']} semble être vide ou trop court.");
        }

        if (in_array(Str::slug($this->data['licence']), $this->licences)) {
            throw new ImportationException("Le code de licence '{$this->data['licence']}' apparait plusieurs fois, vérifiez la casse, et les espaces par exemple.");
        } else {
            $this->licences[] = Str::slug($this->data['licence']);
        }

        $clicenceExists = Licence::where('code', $this->data["licence"])->first();
        if ($clicenceExists) {
            throw new ImportationException("Le code de licence '{$this->data["licence"]}' est déjà pris.");
        }
    }

    private function validateEmailProfesionnel()
    {
        $validator = Validator::make($this->data, [
            "email" => "required|email"
        ]);
        if ($validator->fails()) {
            throw new ImportationException("L'email professionnel de l'auto-école: {$this->data['name']} est invalide.");
        }

        if (in_array($this->data['email'], $this->emailPro)) {
            throw new ImportationException("L'e-mail professionel '{$this->data['email']}' apparait plusieurs fois, vérifiez la casse, et les espaces par exemple.");
        } else {
            $this->emailPro[] = $this->data['email'];
        }
        // Vérifier l'unicité de l'email
        $emailExists = AutoEcole::where('email', $this->data['email'])
            ->first();
        if ($emailExists) {
            throw new ImportationException("L'email professionel {$this->data['email']} est déjà pris, l'auto-école {$this->data['name']} ne peut plus l'utiliser",);
        }
    }

    private function validateAdresse()
    {
        $validator = Validator::make($this->data, [
            "adresse" => "required|min:1"
        ]);
        if ($validator->fails()) {
            throw new ImportationException("L'adresse de l'auto-école '{$this->data['name']}', semble être vide ou trop courte.");
        }
    }
    private function validatePhone()
    {
        $validator = Validator::make($this->data, [
            "phone" => "required|min_digits:4"
        ]);

        if ($validator->fails()) {
            throw new ImportationException("Le numéro téléphone professionnel de l'auto-école '{$this->data['name']}' semble être incorrect ou vide.");
        }
    }

    private function validateNpi()
    {
        $validator = Validator::make($this->data, [
            "npi" => "required"
        ]);

        if ($validator->fails()) {
            throw new ImportationException("Le numéro NPI du promoteur de l'auto-école '{$this->data['name']}' semble être incorrect ou vide.");
        }
    }

    private function validateEmailPromoteur()
    {
        $validator = Validator::make($this->data, [
            "emailPromoteur" => "required|email"
        ]);

        if ($validator->fails()) {
            throw new ImportationException("L'adresse e-mail du promoteur de l'auto-école '{$this->data['name']}' semble être incorrect ou vide.");
        }
    }
    private function validateIfu()
    {
        $validator = Validator::make($this->data, [
            "ifu" => "required"
        ]);

        if ($validator->fails()) {
            throw new ImportationException("Le numéro IFU de l'auto-école '{$this->data['name']}' semble être incorrecte ou vide.");
        }
        $dgi = new Dgi($this->data['ifu']);
        if (!$dgi->exists()) {
            throw new ImportationException("Le numéro IFU de l'auto-école '{$this->data['name']}' n'existe pas chez la DGI.");
        }
        if (!$dgi->raisonSociale()) {
            throw new ImportationException("Le numéro IFU de l'auto-école '{$this->data['name']}' ne disponse pas de raison sociale.");
        }
    }

    private function validateNpiPromoteur()
    {
        $responseP = Api::base('GET', "candidats/" . $this->data['npi']);
        if (!$responseP->successful()) {
            throw new ImportationException("Le numéro NPI du promoteur de l'auto-ecole {$this->data['name']} n'existe pas chez l'ANIP.");
        }
    }

    private function  validatePromoteur()
    {
        $npi = $this->data['npi'];
        $emailPromoteur = $this->data['emailPromoteur'];
        $ifu = $this->data['ifu'];
        if (isset($this->npiEmailIfuMap[$npi])) {
            // Le NPI est déjà présent, vérifions si les adresses e-mail correspondent
            if ($this->npiEmailIfuMap[$npi] !== $emailPromoteur) {
                throw new ImportationException("Le NPI du promoteur $npi est associé à de différentes adresses e-mail.");
            }
        } else {
            $this->npiEmailIfuMap[$npi] = $emailPromoteur;
        }

        if (isset($this->npiEmailIfuMap[$emailPromoteur])) {
            if ($this->npiEmailIfuMap[$emailPromoteur] !== $npi) {
                throw new ImportationException("L'adresse e-mail du promoteur $emailPromoteur est associée à différents npis.");
            }
        } else {
            $this->npiEmailIfuMap[$emailPromoteur] = $npi;
        }

        if (isset($this->npiEmailIfuMap[$ifu])) {
            if ($this->npiEmailIfuMap[$ifu] !== $npi) {
                throw new ImportationException("L'IFU : {$ifu} de l'auto-école {$this->data['name']} est associé a de différents promoteurs");
            }
        } else {
            $this->npiEmailIfuMap[$ifu] = $npi;
        }

        $promoteur = Promoteur::whereEmail($emailPromoteur)->orWhere("npi", $emailPromoteur)->first();
        if ($promoteur) {
            $existsP = Promoteur::where(['email' => $emailPromoteur, "npi" => $npi])->exists();
            if (!$existsP) {
                throw new ImportationException("L'e-mail ou le NPI du promoteur de l'auto-école {$this->data['name']} est déjà pris. Mais le couple npi/email existant, n'est pas conforme à celui dans le fichier excel.");
            }
        }

        $ae = AutoEcole::where('num_ifu', $this->data['ifu'])->first();
        if ($ae) {
            if (!$promoteur) {
                throw new ImportationException("L'IFU : {$this->data['ifu']} de l'auto-école {$this->data['name']} est déjà utilisé par un autre promoteur.");
            }

            if ($promoteur->id != $ae->promoteur_id) {
                throw new ImportationException("L'IFU : {$this->data['ifu']} de l'auto-école {$this->data['name']} est déjà utilisé par un autre promoteur.");
            }
        }
    }

    private function validateAgrement()
    {
        $validator = Validator::make($this->data, [
            "agrement" => "required|min:3"
        ]);

        if ($validator->fails()) {
            throw new ImportationException("Le code d'agrément de l'auto-école: {$this->data['name']} semble être vide ou trop court.");
        }

        if (in_array(Str::slug($this->data['agrement']), $this->agrements)) {
            throw new ImportationException("Le code d'agrément '{$this->data['agrement']}' de l'auto-école {$this->data['name']} apparait plusieurs fois dans le fichier.");
        } else {
            $this->agrements[] = Str::slug($this->data['agrement']);
        }
        $agrementExists = Agrement::where('code', $this->data['agrement'])->first();
        if ($agrementExists) {
            throw new ImportationException("Le code d'agrément '{$this->data['agrement']}' de l'auto-école {$this->data['name']} est déjà utilisé par une autre auto-école.");
        }
    }

    private function validateImmatriculations()
    {
        $imatriculations = explode(",", $this->data["immatriculations"]);
        $imatriculations = array_map(function ($item) {
            return trim($item);
        }, $imatriculations);
        $imatriculations = array_filter($imatriculations);
        if (!$imatriculations) {
            throw new ImportationException("Le(s) immatriculation(s) de l'auto-école: {$this->data['name']} semble(nt) être vide(s) ou trop courte(s).");
        }
        foreach ($imatriculations as $key => $v) {
            if (in_array($v, $this->vehicules)) {
                throw new ImportationException(
                    "L'immatriculation {$v} de l'auto-école: {$this->data['name']} apparaît plusieurs fois."
                );
            } else {
                if (strlen($v) < 4) {
                    throw new ImportationException(
                        "L'immatriculation {$v} n'a pas au moins 4 caractères. Auto-ecole: {$this->data['name']}"
                    );
                }
                $this->vehicules[] = $v;
            }
            $vehiculeExists =  Vehicule::where('immatriculation', $v)->first();
            if ($vehiculeExists) {
                throw new ImportationException("L'immatriculation $v de l'auto-école {$this->data['name']} est déjà associé a une autre auto école.",);
            }
        }
        $this->data['vehicules'] = $this->vehicules;
    }

    private function validateDate()
    {
        $date = $this->data['data_licence'];
        //Excel convertir les bonnes dates en chains numériques
        $validator = Validator::make($this->data, [
            "data_licence" => "required|numeric"
        ]);
        if ($validator->fails()) {
            throw new ImportationException("La date d'expiration de licence de l'auto-école: {$this->data['name']} semble être vide ou invalide.");
        }
        $ca = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
        if ($ca->diffInDays() > 365) {
            throw new ImportationException("La date d'expiration de licence de l'auto-école: {$this->data['name']} doit être inférieure à 1an.");
        }

        $this->data = array_merge($this->data, [
            "date_licence" => $ca
        ]);
    }
}
