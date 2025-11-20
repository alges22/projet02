<?php

use App\Http\Controllers\Composition\ListeEmargementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CountController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\LangueController;
use App\Http\Controllers\CommuneController;
use App\Http\Controllers\ChapitreController;
use App\Http\Controllers\AutoEcoleController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\AgregateurController;
use App\Http\Controllers\SalleCompoController;
use App\Http\Controllers\TrancheAgeController;
use App\Http\Controllers\AnnexeAnattController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\CentreExamenController;
use App\Http\Controllers\JuryCandidatController;
use App\Http\Controllers\DemandePermisController;
use App\Http\Controllers\ProgrammationController;
use App\Http\Controllers\ValidationCedController;
use App\Http\Controllers\ArrondissementController;
use App\Http\Controllers\BaremeConduiteController;
use App\Http\Controllers\CandidatConduiteDetailRpseController;
use App\Http\Controllers\CodeInspectionController;
use App\Http\Controllers\DossierSessionController;
use App\Http\Controllers\CategoriePermisController;
use App\Http\Controllers\JuryDistributionController;
use App\Http\Controllers\ConduiteInspectionController;
use App\Http\Controllers\CatPermisTrancheAgeController;
use App\Http\Controllers\Permis\PermisDetailsController;
use App\Http\Controllers\ProgrammationConduiteController;
use App\Http\Controllers\Resultats\StatistiqueController;
use App\Http\Controllers\Resultats\ResultatCodeController;
use App\Http\Controllers\CandidatConduiteReponseController;
use App\Http\Controllers\CandidatController;
use App\Http\Controllers\Composition\EmargementController;
use App\Http\Controllers\JuryDistributionByVagueController;
use App\Http\Controllers\Permis\CreatePermisController;
use App\Http\Controllers\Programmation\ConvocationController;
use App\Http\Controllers\Resultats\ResultatConduiteController;
use App\Http\Controllers\Programmation\SalleDistributionController;
use App\Http\Controllers\ProgrammationConduiteByVagueController;
use App\Http\Controllers\Resultats\DeliberationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('anatt-base')->group(function () {

    Route::middleware('auth.api.client')->group(function () {

        Route::match(['GET', 'POST'], '/candidats', [CandidatController::class, 'index']);
        Route::match(['GET', 'POST'], '/candidats-images', [CandidatController::class, 'anipImage']);
        Route::get('/candidats/{npi}', [CandidatController::class, 'getInformations']);
        Route::get('counts', CountController::class);

        Route::get('/departements', [DepartementController::class, 'index']);
        Route::get('/departements/{id}', [DepartementController::class, 'show']);
        Route::post('/departements', [DepartementController::class, 'store']);
        Route::put('/departements/{id}', [DepartementController::class, 'update']);
        Route::delete('/departements/{id}', [DepartementController::class, 'destroy']);

        Route::get('/communes', [CommuneController::class, 'index']);
        Route::get('/communes/{id}', [CommuneController::class, 'show']);
        Route::post('/communes', [CommuneController::class, 'store']);
        Route::put('/communes/{id}', [CommuneController::class, 'update']);
        Route::delete('/communes/{id}', [CommuneController::class, 'destroy']);

        Route::get('/arrondissements', [ArrondissementController::class, 'index']);
        Route::get('/arrondissements/{id}', [ArrondissementController::class, 'show']);
        Route::post('/arrondissements', [ArrondissementController::class, 'store']);
        Route::put('/arrondissements/{id}', [ArrondissementController::class, 'update']);
        Route::delete('/arrondissements/{id}', [ArrondissementController::class, 'destroy']);

        Route::get('/examens', [ExamenController::class, 'index']);
        Route::get('/examens/session-en-cours', [ExamenController::class, 'currentExamen']);
        Route::get('/examens/{id}', [ExamenController::class, 'show']);
        Route::post('/examens', [ExamenController::class, 'store']);
        Route::put('/examens/{id}', [ExamenController::class, 'update']);
        Route::delete('/examens/{id}', [ExamenController::class, 'destroy']);

        Route::get('/langues', [LangueController::class, 'index']);
        Route::post('/langues/status', [LangueController::class, 'status']);
        Route::get('/langues/{id}', [LangueController::class, 'show']);
        Route::post('/langues', [LangueController::class, 'store']);
        Route::put('/langues/{id}', [LangueController::class, 'update']);
        Route::delete('/langues/{id}', [LangueController::class, 'destroy']);

        Route::get('/centre-examens', [CentreExamenController::class, 'index']);
        Route::get('/centre-examens/{id}', [CentreExamenController::class, 'show']);
        Route::post('/centre-examens', [CentreExamenController::class, 'store']);
        Route::put('/centre-examens/{id}', [CentreExamenController::class, 'update']);
        Route::delete('/centre-examens/{id}', [CentreExamenController::class, 'destroy']);

        Route::get('/salle-compos', [SalleCompoController::class, 'index']);
        Route::get('/annexeanatt-salle-compos/{annexe_anatt_id}', [SalleCompoController::class, 'getAnnexeAnattSalles']);
        Route::get('/salle-compos/{id}', [SalleCompoController::class, 'show']);
        Route::post('/salle-compos', [SalleCompoController::class, 'store']);
        Route::put('/salle-compos/{id}', [SalleCompoController::class, 'update']);
        Route::delete('/salle-compos/{id}', [SalleCompoController::class, 'destroy']);
        Route::post('/salle-compo/multiple', [SalleCompoController::class, 'storeMultiple']);

        Route::prefix('categorie-permis')->group(function () {
            Route::get('/', [CategoriePermisController::class, 'index']);
            Route::get('/extensions', [CategoriePermisController::class, 'getExtension']);
            Route::post('/extension', [CategoriePermisController::class, 'storeExtension']);
            Route::delete('/extension/{id}', [CategoriePermisController::class, 'destroyExtension']);
            Route::get('/{id}', [CategoriePermisController::class, 'show']);
            Route::post('/', [CategoriePermisController::class, 'store']);
            Route::put('/{id}', [CategoriePermisController::class, 'update']);
            Route::delete('/{id}', [CategoriePermisController::class, 'destroy']);
        });
        Route::get('/candidat-permis/{candidatId}/{permisPrealableId}', [CreatePermisController::class, 'checkPermisCombination']);


        Route::get('/tranche-ages', [TrancheAgeController::class, 'index']);
        Route::post('/tranche-ages/status', [TrancheAgeController::class, 'status']);
        Route::get('/tranche-ages/{id}', [TrancheAgeController::class, 'show']);
        Route::post('/tranche-ages', [TrancheAgeController::class, 'store']);
        Route::put('/tranche-ages/{id}', [TrancheAgeController::class, 'updateTrancheAge']);
        Route::delete('/tranche-ages/{id}', [TrancheAgeController::class, 'destroy']);

        Route::get('/cat-permis-tranches', [CatPermisTrancheAgeController::class, 'index']);
        Route::get('/cat-permis-tranches/{id}', [CatPermisTrancheAgeController::class, 'show']);
        Route::post('/cat-permis-tranches', [CatPermisTrancheAgeController::class, 'store']);
        Route::put('/cat-permis-tranches/{id}', [CatPermisTrancheAgeController::class, 'update']);
        Route::delete('/cat-permis-tranches/{id}', [CatPermisTrancheAgeController::class, 'destroy']);


        Route::get('/bareme-conduites', [BaremeConduiteController::class, 'index']);
        Route::get('/bareme-conduites/categorie-permis/{categorie_permis_id}', [BaremeConduiteController::class, 'getByCategoriePermisId']);
        Route::get('/bareme-conduites/{id}', [BaremeConduiteController::class, 'show']);
        Route::post('/bareme-conduites', [BaremeConduiteController::class, 'store']);
        Route::post('/bareme-conduite', [BaremeConduiteController::class, 'addBareme']);
        Route::put('/bareme-conduites/{id}', [BaremeConduiteController::class, 'update']);
        Route::delete('/bareme-conduites/{id}', [BaremeConduiteController::class, 'destroy']);

        Route::get('/agregateurs', [AgregateurController::class, 'index']);
        Route::get('/agregateurs/{id}', [AgregateurController::class, 'show']);
        Route::post('/agregateurs', [AgregateurController::class, 'store']);
        Route::post('/agregateurs/status', [AgregateurController::class, 'status']);
        Route::match(['post', 'put'], '/agregateurs/{id}', [AgregateurController::class, 'update']);
        Route::delete('/agregateurs/{id}', [AgregateurController::class, 'destroy']);

        Route::get('/parametres', [ParametreController::class, 'index']);
        Route::get('/parametres/{id}', [ParametreController::class, 'show']);
        Route::post('/parametres', [ParametreController::class, 'store']);
        Route::put('/parametres/{id}', [ParametreController::class, 'update']);
        Route::delete('/parametres/{id}', [ParametreController::class, 'destroy']);


        Route::prefix('chapitres')->group(function () {
            Route::get('/', [ChapitreController::class, 'index']);
            Route::get('/get-many', [ChapitreController::class, 'getMany']);
            Route::post('/', [ChapitreController::class, 'store']);
            Route::post('/chap-question-counts', [ChapitreController::class, 'chapQuestions']);
            Route::get('/{id}', [ChapitreController::class, 'show']);
            Route::put('/{id}', [ChapitreController::class, 'update']);
            Route::delete('/{id}', [ChapitreController::class, 'destroy']);
        });

        #Conduite reponse
        Route::prefix('candidat-conduite-reponses')->group(function () {
            Route::get('/{jury_candidat_id}', [CandidatConduiteReponseController::class, 'show']);
            // Route::post('/', [CandidatConduiteReponseController::class, 'store']);
            Route::post('/', [CandidatConduiteDetailRpseController::class, 'store']);
        });

        # Programmation
        Route::prefix('programmations')->group(function () {
            Route::post('generate', [ProgrammationController::class, 'generate']);
            //Une seule responsabilité distributer les candidats dans les salles
            Route::post('distribute-into-salle', SalleDistributionController::class);
            Route::get('statistiques', [ProgrammationController::class, 'statistiques']);
            Route::get('/', [ProgrammationController::class, 'vagues']);
        });


        #  conduite
        Route::post('programmation-conduite-byvague', [ProgrammationConduiteByVagueController::class, 'generate']);
        Route::post('jury-distribution-byvague', [JuryDistributionByVagueController::class,'__invoke']);
        Route::prefix('conduite')->group(function () {
            Route::post('generate', [ProgrammationConduiteController::class, 'generate']);
            Route::post('jury-distribution', JuryDistributionController::class);

            Route::get('resultat-code', [ProgrammationConduiteController::class, 'resultatCode']);
            Route::get('programmations', [ProgrammationConduiteController::class, 'vagues']);
        });
        Route::post('parcours-suivis/resultats-codes', [DossierSessionController::class, 'resultatCode']);
        Route::post('dossier-sessions/suivi-candidat/state', [DossierSessionController::class, 'updatStateSuiviCandidat']);
        Route::prefix('dossier-sessions')->group(function () {
            Route::get('/', [DossierSessionController::class, 'index']);
            Route::get('/monitoring', [DossierSessionController::class, 'index']);
            Route::get('/{dossier_session_id}', [DossierSessionController::class, 'show']);
            Route::post('/state', [DossierSessionController::class, 'updateStateSuiviCandidat']);
        });
        # Route::get('/dossier-candidat/{dossier_id}/full', [DossierSessionController::class, 'fullDossier']);
        Route::get('/dossier-candidats/{id}', [DossierSessionController::class, 'showDossier']);


        Route::match(['post', 'put'], '/update-dossier-state', [DossierSessionController::class, 'updateState']);
        Route::match(['post', 'put'], '/updat-dossier-state', [DossierSessionController::class, 'updateStateCed']);
        # Suivi candidat
        Route::prefix('suivi-candidats')->group(function () {
            Route::get('/', [DossierSessionController::class, 'suivisCandidats']);
        });

        ## Auto écoles
        Route::prefix('auto-ecoles')->group(function () {
            Route::get('/', [AutoEcoleController::class, 'index']);
            Route::get('/{id}/candidats', [AutoEcoleController::class, 'candidats']);
        });

        /**
         * Validation CED
         */
        Route::prefix('validation-ced')->group(function () {
            Route::get('/', [ValidationCedController::class, 'index']);
            Route::post('/validation', [ValidationCedController::class, 'validation']);
        });
        /**
         * Jury Candidat
         */
        Route::prefix('jury')->group(function () {
            Route::get('/', [JuryCandidatController::class, 'index']);
            Route::get('/{id}', [JuryCandidatController::class, 'show']);
            Route::post('dossiers', [JuryCandidatController::class, 'getDossierbyJury']);
            Route::post('dossiers-noter', [JuryCandidatController::class, 'getNotedDossierbyJury']);
        });

        # Inspection

        Route::prefix('code-inspections')->group(function () {
            Route::get("salles", [CodeInspectionController::class, 'salles']);
            Route::middleware('has.inspector.access')->group(function () {
                Route::get("agendas", [CodeInspectionController::class, 'agendas']);
                Route::get("vagues", [CodeInspectionController::class, 'vagues']);
                Route::get('recapts', [CodeInspectionController::class, 'recapts']);
                Route::post('mark-as-abscent', [CodeInspectionController::class, 'markAsAbscent']);
                Route::post('open-session', [CodeInspectionController::class, 'openSession']);
                Route::post('stop-candidat-compo', [CodeInspectionController::class, 'stopCandidatCompo']);
                Route::post('emarges', EmargementController::class);
                Route::post('pause', [CodeInspectionController::class, 'pause']);
                Route::post('reset-compo', [CodeInspectionController::class, 'resetCompo']);
            });
        });

        # Inspection Conduite
        Route::prefix('conduite-inspections')->group(function () {

            Route::middleware('has.examinator.access')->group(function () {
                Route::get('recapts', [ConduiteInspectionController::class, 'recapts']);
                Route::get('agendas', [ConduiteInspectionController::class, 'agendas']);
                Route::get('vagues', [ConduiteInspectionController::class, 'vagues']);
            });
        });

        Route::prefix('annexe-anatts')->group(function () {
            Route::get('/{id}/salles-inspecteurs', [AnnexeAnattController::class, 'salleInspecteurs']);
        });

        Route::prefix('resultats')->group(function () {
            Route::get('codes', [ResultatCodeController::class, 'index']);
            Route::get('conduites', [ResultatConduiteController::class, 'index']);
            Route::get('statistic-code', [StatistiqueController::class, 'codes']);
            Route::get('statistic-conduite', [StatistiqueController::class, 'conduites']);
            Route::get('candidats/{dsId}', [ResultatCodeController::class, 'candidats']);

            Route::get('admis', [DeliberationController::class, 'admis']);
            Route::get('recales', [DeliberationController::class, 'recales']);
            Route::get('list-emargement', ListeEmargementController::class);
        });

        Route::prefix('demande-permis')->group(function () {
            Route::get('/', [DemandePermisController::class, 'index']);
            Route::post('/', [DemandePermisController::class, 'store']);
            Route::post('/check-permis', [DemandePermisController::class, 'checkPermis']);
        });
        Route::post('candidat/permis', [DemandePermisController::class, 'getUserPermis']);


        Route::post('convocations', [ConvocationController::class, 'sendConvocations']);
        Route::post('conduite/convocations', [ConvocationController::class, 'sendConduiteConvocations']);

        # Permis / Permis numériques
        Route::prefix('permis')->group(function () {
            Route::get('{code_permis}', PermisDetailsController::class);
        });
    });



    Route::get('/api/documentation', function () {
        return view('vendor.l5-swagger.index');
    });
});

# Pour les apis externes
require __DIR__  . "/external.php";
# Pour les tests
require __DIR__  . "/tests.php";
