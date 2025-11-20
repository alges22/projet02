<?php

namespace App\Services\Permis;

use App\Models\Permis;
use App\Models\TrancheAge;
use App\Models\JuryCandidat;
use Illuminate\Support\Carbon;
use App\Models\CategoriePermis;
use App\Models\Admin\AnnexeAnatt;
use App\Models\Candidat\Candidat;
use App\Models\Candidat\DossierSession;
use App\Models\Candidat\DossierCandidat;
use App\Services\Exceptions\GlobalException;
use App\Services\GetCandidat;

class CreatePermis
{
    public function __construct(private $dossier_session_id) {}
    public function create()
    {

        /**
         * @var DossierSession $candidat
         */
        $candidat = DossierSession::find($this->dossier_session_id);
        $annexe = AnnexeAnatt::find($candidat->annexe_id);

        $user = Candidat::where('npi', $candidat->npi)->first();
        $dossier = DossierCandidat::find($candidat->dossier_candidat_id);

        $dossier->update([
            'state' => "success",
        ]);

        $info = GetCandidat::findOne($candidat->npi);

        $juryCandidat = JuryCandidat::where('dossier_session_id', $this->dossier_session_id)->first();

        $data = [
            "examen_id" => $candidat['examen_id'],
            "dossier_session_id" => $this->dossier_session_id,
            'categorie_permis_id' => $candidat['categorie_permis_id'],
            'jury_candidat_id' => $juryCandidat->id,
            'npi' => $candidat['npi'],
            'code_permis' => $this->generateCodePermis($candidat['npi'], $annexe, $user->sexe),
            'deliver_id' => $this->signataireId(),
            'signed_at' => now(),
            'signataire_id' => $this->signataireId(),
            'delivered_at' => now(),
            'expired_at' => $this->getExpiredAt($candidat['categorie_permis_id'], $info['date_de_naissance']),
        ];
        // Vérification et création du permis B si nécessaire
        $categoriePermis = CategoriePermis::find($candidat['categorie_permis_id']);
        $categoriesArequiertPermisB = ['B1', 'C', 'CE', 'D'];

        if (in_array($categoriePermis->name, $categoriesArequiertPermisB)) {
            // Vérifier si le candidat a déjà un permis B en utilisant categorie_permis_id
            $permisB = Permis::where('npi', $candidat->npi)
                ->where('categorie_permis_id', CategoriePermis::where('name', 'B')->first()->id)
                ->first();

            if (!$permisB) {
                // Créer un permis B avant de créer la nouvelle catégorie
                $this->createPermisB($candidat, $annexe, $user, $juryCandidat, $info);
            }
        }

        Permis::create($data);
        if ($candidat->permis_extension_id) {
            $data['categorie_permis_id'] = $candidat->permis_extension_id;
            $data['expired_at'] = $this->getExpiredAt($candidat->permis_extension_id, $info['date_de_naissance']);
            Permis::create($data);
        }

        $this->updateDossier($candidat->dossier_candidat_id, 'success');

        return [];
    }

    // Fonction pour créer un permis B
    private function createPermisB($candidat, $annexe, $user, $juryCandidat, $info)
    {
        $dataB = [
            "examen_id" => $candidat['examen_id'],
            "dossier_session_id" => $this->dossier_session_id,
            'categorie_permis_id' => CategoriePermis::where('name', 'B')->first()->id,
            'jury_candidat_id' => $juryCandidat->id,
            'npi' => $candidat['npi'],
            'code_permis' => $this->generateCodePermis($candidat['npi'], $annexe, $user->sexe),
            'deliver_id' => $this->signataireId(),
            'signed_at' => now(),
            'signataire_id' => $this->signataireId(),
            'delivered_at' => now(),
            'expired_at' => $this->getExpiredAt($candidat['categorie_permis_id'], $info['date_de_naissance']),
        ];

        // Créer le permis B
        Permis::create($dataB);
    }

    private function generateCodePermis($npi, AnnexeAnatt $annexe, $sexe)
    {
        // Si le candidat avait un permis
        $permis = Permis::whereNpi($npi)->first();
        if ($permis) {
            return $permis->code_permis;
        }


        $departement = $this->getDepartement($annexe);
        $idDepartement = $departement['idDepartement'];
        $sexeCode = $sexe == 'M' ? '1' : '2';
        $year = date('y');
        $operation = "01"; //permis succès

        return "A{$npi}{$sexeCode}{$idDepartement}{$year}{$operation}";
    }

    private function updateDossier($id, $state)
    {
        $dsc = DossierCandidat::find($id);
        if ($dsc->state == 'success') {
            return;
        }
        $dsc->state = $state;
        $dsc->save();
    }
    private function signataireId()
    {
        return 1;
    }

    private function getDepartement(AnnexeAnatt $annexe): array
    {
        // Récupérer la collection de départements associés à l'annexe
        $departementCollection = $annexe->getDepartements();

        // Transformer la collection en tableau contenant les noms des départements
        $departmentNames = $departementCollection->pluck('name')->toArray();

        // Tableau des départements
        $departements = [
            [
                'idDepartement' => '01',
                'LibelleDepartement' => ['ATACORA', 'DONGA'],
            ],
            [
                'idDepartement' => '02',
                'LibelleDepartement' => ['LITTORAL'],
            ],
            [
                'idDepartement' => '03',
                'LibelleDepartement' => ['BORGOU', 'ALIBORI'],
            ],
            [
                'idDepartement' => '04',
                'LibelleDepartement' => ['ZOU', 'COLLINES'],
            ],
            [
                'idDepartement' => '05',
                'LibelleDepartement' => ['MONO', 'COUFFO'],
            ],
            [
                'idDepartement' => '06',
                'LibelleDepartement' => ['OUEME', 'PLATEAU'],
            ],
            [
                'idDepartement' => '07',
                'LibelleDepartement' => ['ETRANGER'],
            ],
            [
                'idDepartement' => '08',
                'LibelleDepartement' => ['ALIBORI'],
            ],
            [
                'idDepartement' => '09',
                'LibelleDepartement' => ['FORCES ARMEES BENINOISES'],
            ],
            [
                'idDepartement' => '10',
                'LibelleDepartement' => ['ATLANTIQUE'],
            ]
        ];

        // Chercher le département correspondant
        foreach ($departements as $departement) {
            // Vérifier si l'un des noms dans 'LibelleDepartement' correspond à un des noms dans $departmentNames
            foreach ($departement['LibelleDepartement'] as $libelle) {
                if (in_array($libelle, $departmentNames)) {
                    // Retourner le département correspondant
                    return $departement;
                }
            }
        }

        throw new GlobalException('Aucun département trouvé pour cette annexe');
    }

    private function getExpiredAt($permisId, $birthday)
    {
        $birthdayCarbon = Carbon::parse($birthday);
        $age = $birthdayCarbon->age;
        $trancheage = TrancheAge::where('categorie_permis_id', $permisId)->get();

        if ($trancheage->isNotEmpty()) {
            $t = $trancheage->whereNotNull('age_min')
                ->whereNotNull('age_max')
                ->where('age_min', '<=', $age)->where('age_max', '>=', $age)->first();
            if (!$t) {
                $t = $trancheage->first();
            }
            return $t ? now()->addYears($t->validite) : null;
        }
        return null;
    }
}
