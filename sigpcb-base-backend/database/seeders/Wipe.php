<?php

namespace Database\Seeders;

use App\Models\Vague;
use App\Models\Permis;
use App\Models\CompoPage;
use App\Models\CompoToken;
use App\Models\Admin\Jurie;
use App\Models\Admin\Examen;
use App\Models\AncienPermis;
use App\Models\CompoSession;
use App\Models\JuryCandidat;
use App\Models\PromoteurIfu;
use App\Models\ResultatCode;
use App\Models\ConduiteVague;
use App\Models\DemandePermis;
use App\Models\CandidatReponse;
use App\Models\InspecteurSalle;
use Illuminate\Database\Seeder;
use App\Models\Admin\Inspecteur;
use App\Models\Admin\MoniteurDb;
use App\Models\CandidatQuestion;
use App\Models\Admin\Examinateur;
use App\Models\Candidat\Candidat;
use App\Models\AutoEcole\Agrement;
use App\Models\AutoEcole\Vehicule;
use App\Models\VagueSalleQuestion;
use App\Models\AnnexeResultatState;
use App\Models\AutoEcole\AutoEcole;
use App\Models\AutoEcole\Promoteur;
use App\Models\CandidatExamenSalle;
use App\Models\Candidat\CandidatOtp;
use App\Models\AutoEcole\MoniteurOtp;
use App\Models\AutoEcole\VerifyPhone;
use App\Models\AutoEcole\AutoEcoleOtp;
use App\Models\AutoEcole\OldAutoEcole;
use App\Models\Candidat\ParcoursSuivi;
use App\Models\AutoEcole\AutoEcoleInfo;
use App\Models\AutoEcole\MoniteurToken;
use App\Models\AutoEcole\SuiviCandidat;
use App\Models\Candidat\DossierSession;
use App\Models\CandidatConduiteReponse;
use App\Models\AutoEcole\DemandeLicence;
use App\Models\Candidat\CandidatPayment;
use App\Models\Candidat\ConvocationCode;
use App\Models\Candidat\DossierCandidat;
use App\Models\CompoCandidatDeconnexion;
use App\Models\AutoEcole\DemandeAgrement;
use App\Models\Candidat\ParcoursCandidat;
use App\Models\Candidat\PermisNumPayment;
use App\Models\AutoEcole\AutoEcoleLicence;
use App\Models\AutoEcole\AutoEcolePayment;
use App\Models\Candidat\DossierMotifRejet;
use App\Models\AutoEcole\AutoEcoleInactive;
use App\Models\AutoEcole\AutoEcoleMoniteur;
use App\Models\AutoEcole\DemandeLicenceFile;
use App\Models\AutoEcole\DemandeAgrementFile;
use App\Models\AutoEcole\DemandeLicenceRejet;
use App\Models\AutoEcole\DemandeAgrementRejet;
use App\Models\AutoEcole\AutoEcoleNotification;
use App\Models\AutoEcole\MoniteurSuiviCandidat;
use App\Models\Admin\ExaminateurCategoriePermis;
use App\Models\AutoEcole\AutoEcoleCandidatInscription;
use App\Models\Question;
use App\Models\QuestionCache;
use App\Models\QuestionLangue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class Wipe extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        foreach ($this->base() as $key => $model) {
            app($model)->truncate();
        }
    }


    private function admin(): array
    {
        return [
            Inspecteur::class,
            InspecteurSalle::class,
            Examinateur::class,
            ExaminateurCategoriePermis::class,
            Jurie::class,

        ];
    }

    private function base(): array
    {
        return [
            MoniteurDb::class,
            Inspecteur::class,
            InspecteurSalle::class,
            Examinateur::class,
            ExaminateurCategoriePermis::class,
            Jurie::class,
            CompoSession::class,
            CompoCandidatDeconnexion::class,
            CompoPage::class,
            CompoToken::class,
            CandidatQuestion::class,
            CandidatReponse::class,
            CandidatExamenSalle::class,
            VagueSalleQuestion::class,
            CandidatConduiteReponse::class,
            ConduiteVague::class,
            JuryCandidat::class,
            ResultatCode::class,
            Vague::class,
            DemandePermis::class,
            AnnexeResultatState::class,
            Permis::class,
            ConvocationCode::class,
            ParcoursSuivi::class,
            ParcoursCandidat::class,
            PermisNumPayment::class,
            CandidatPayment::class,
            AncienPermis::class,
            CandidatConduiteReponse::class,
            ConduiteVague::class,
            ResultatCode::class,
            DossierMotifRejet::class,
            MoniteurSuiviCandidat::class,
            DemandeLicence::class,
            DemandeLicenceRejet::class,
            Vehicule::class,
            DemandeLicenceFile::class,
            AutoEcolePayment::class,
            AutoEcoleLicence::class,
            AutoEcoleCandidatInscription::class,
            MoniteurToken::class,
            MoniteurOtp::class,
            AutoEcoleOtp::class,
            AutoEcoleMoniteur::class,
            VerifyPhone::class,
            OldAutoEcole::class,
            DemandeAgrementRejet::class,
            DemandeAgrementFile::class,
            DemandeAgrement::class,
            Agrement::class,
            AutoEcoleNotification::class,
            AutoEcoleInactive::class,
            AutoEcoleInfo::class,
            SuiviCandidat::class,
            AutoEcole::class,
            Promoteur::class,
            PromoteurIfu::class,
            DossierSession::class,
            DossierCandidat::class,
            CandidatOtp::class,
            Candidat::class,
            Examen::class,
            // QuestionLangue::class,
            // Question::class,
        ];
    }
}
