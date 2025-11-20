<?php

use App\Http\Controllers\AnipImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CountController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\EchangeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CandidatController;
use App\Http\Controllers\NpiANaTTController;
use App\Http\Controllers\WebHooksController;
use App\Http\Controllers\AutoEcoleController;
use App\Http\Controllers\DuplicataController;
use App\Http\Controllers\ReconduitController;
use App\Http\Controllers\GenerationController;
use App\Http\Controllers\AnnexeAnattController;
use App\Http\Controllers\FindPaymentController;
use App\Http\Controllers\ProrogationController;
use App\Http\Controllers\RestrictionController;
use App\Http\Controllers\AuthenticiteController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\CandidatVagueController;
use App\Http\Controllers\ParcoursSuiviController;
use App\Http\Controllers\DossierSessionController;
use App\Http\Controllers\CandidatPaymentController;
use App\Http\Controllers\CandidatReponseController;
use App\Http\Controllers\DossierCandidatController;
use App\Http\Controllers\ParcoursCandidatController;
use App\Http\Controllers\PermisNumPaymentController;
use App\Http\Controllers\CandidatEcritNoteController;
use App\Http\Controllers\DossierMotifRejetController;
use App\Http\Controllers\PermisInternationalController;
use App\Http\Controllers\CandidatJustifAbsenceController;
use App\Http\Controllers\CandidatJustifController;
use App\Http\Controllers\DispensePaiementController;
use App\Http\Controllers\SuccesAttestationController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::prefix('anatt-candidat')->group(function () {

    Route::post('/login', [RegistrationController::class, 'login']);
    Route::post('/register', [RegistrationController::class, 'register']);
    Route::post('/verify-otp', [RegistrationController::class, 'verifyOtp']);
    Route::post('/resend-code', [RegistrationController::class, 'resendCode']);
    Route::post('/verify-npi', [RegistrationController::class, 'verifyNpi']);
    Route::post('/npi-candidat', [NpiANaTTController::class, 'getNPI']);
    // Route::get('/users', [UserController::class, 'index']);
    // Route::get('/candidats/{id}', [CandidatController::class, 'show']);
    // Route::get('/candidats/npi/{npi}', [CandidatController::class, 'showByNpi']);
    Route::get('/faqs', [FaqController::class, 'index']);

    # Unite admin routes
    // Route::prefix('agregateurs-base')->group(function () {
    //     Route::get('/', [BaseController::class, 'index']);
    // });

    Route::get('payments/procced/', [AuthenticiteController::class, "procced"])->name('payments.procced');
    Route::get('payments/piprocced/', [PermisInternationalController::class, "procced"])->name('payments.piprocced');
    Route::get('payments/eprocced/', [EchangeController::class, "procced"])->name('payments.eprocced');
    Route::get('payments/dprocced/', [DuplicataController::class, "procced"])->name('payments.dprocced');
    Route::get('payments/prprocced/', [ProrogationController::class, "procced"])->name('payments.prprocced');
    Route::get('payments/prprocced/', [CandidatPaymentController::class, "procced"])->name('payments.codecprocced');
    Route::get('payments/aprocced/', [SuccesAttestationController::class, "procced"])->name('payments.aprocced');

    // Route::middleware('auth:sanctum')->group(function () {
    Route::middleware(['auth:sanctum',\App\Http\Middleware\CheckUserActivity::class])->group(function () {
        // Route::get('/candidat-parcours', [ParcoursCandidatController::class, 'index']);

        Route::get('user/picture/', [AnipImageController::class, "index"]);
        Route::get('/check-validated-dispense', [DispensePaiementController::class, "checkValidatedDispense"]);
        Route::post('/candidat-paiement/dispenses', [DispensePaiementController::class, "updateCandidatSession"]);

        Route::prefix('bareme-conduites/categorie-permis')->group(function () {
            Route::get('/{categorie_permis_id}', [BaseController::class, 'getBaremeConduiteByPermis']);
        });
        Route::prefix('langues-base')->group(function () {
            Route::get('/', [BaseController::class, 'getLangues']);
        });
        Route::prefix('categorie-permis-base')->group(function () {
            Route::get('/', [BaseController::class, 'getCatPermis']);
        });
        Route::prefix('restrictions')->group(function () {
            Route::get('/', [RestrictionController::class, 'index']);
        });

        Route::prefix('parcours-suivis')->group(function () {
            // Route::get('/', [ParcoursSuiviController::class, 'index']);
            // Route::post('/convocation-codes', [ParcoursSuiviController::class, 'storeConvocationCode']);
            // Route::post('/convocation-conduites', [ParcoursSuiviController::class, 'storeConvocationConduite']);
            // Route::post('/resultats-codes', [ParcoursSuiviController::class, 'resultatCode']);
            // Route::post('/absences', [ParcoursSuiviController::class, 'storeJustif']);
            // Route::get('/all', [ParcoursSuiviController::class, 'getAll']);
            // Route::post('/', [ParcoursSuiviController::class, 'store']);
            Route::get('/{candidat_id}', [ParcoursSuiviController::class, 'show']);
        });
        // Route::get('/dossier-candidat/{dossier_id}/full', [DossierCandidatController::class, 'fullDossier']);
        // Route::get('/dossier-candidats', [DossierCandidatController::class, 'index']);
        Route::get('/dossier-session/{dossier_session_id}', [DossierCandidatController::class, 'getDossierSessionInformation']);
        Route::match(['post', 'put'], '/dossier-session/{dossier_session_id}', [DossierCandidatController::class, 'updateDossierSession']);
        Route::get('/dossier-candidats-souscriptions/{id}', [DossierCandidatController::class, 'getDossiersByCandidatId']);
        Route::get('/dossier-candidats-byautoecole/{id}', [DossierCandidatController::class, 'getDossiersByAutoEcoleId']);
        Route::post('/check-permis-prealable', [DossierCandidatController::class, 'checkPermisPrealable']);
        Route::get('/dossier-candidats/{id}', [DossierCandidatController::class, 'show']);
        Route::delete('/dossier-candidats/{id}', [DossierCandidatController::class, 'destroy']);

        // Route::match(['post', 'put'], '/update-dossier-state', [DossierCandidatController::class, 'updateState']);
        // Route::match(['post', 'put'], '/updat-dossier-state', [DossierCandidatController::class, 'updateStateCed']);

        // Route::get('/init-dossier-candidats-byautoecole/{id}', [DossierCandidatController::class, 'getInitDossiersByAutoEcoleId']);
        // Route::get('/pending-dossier-candidats-byautoecole/{id}', [DossierCandidatController::class, 'getPendingDossiersByAutoEcoleId']);
        // Route::get('/validate-dossier-candidats-byautoecole/{id}', [DossierCandidatController::class, 'getValidateDossiersByAutoEcoleId']);
        // Route::get('/rejet-dossier-candidats-byautoecole/{id}', [DossierCandidatController::class, 'getRejetDossiersByAutoEcoleId']);
        Route::get('counts', CountController::class);


        Route::prefix('dossier-sessions')->group(function () {
            Route::get('/', [DossierSessionController::class, 'index']);

            Route::prefix('{id}')->group(function () {
                Route::get('/', [DossierSessionController::class, 'show']);
                // Route::get('/full-details', [DossierSessionController::class, 'fullDetails']);
            });
        });

        Route::prefix('settings')->group(function () {
            Route::get("/", [SettingController::class, "index"]);
        });
        # annexe-annats
        Route::prefix('annexe-anatts')->group(function () {
            Route::get('/', [AnnexeAnattController::class, 'index']);
            Route::get('/{id}', [AnnexeAnattController::class, 'show']);
        });
        Route::prefix('autoecoles')->group(function () {
            Route::get('/', [AutoEcoleController::class, 'index']);
        });

        Route::post('/justification-absences', [CandidatJustifController::class, 'store']);

        Route::post('/dossier-candidats/justification-paiement', [DossierCandidatController::class, 'closeAndCreateDossierSession']);
        Route::post('/dossier-candidats/expire-paiement', [DossierCandidatController::class, 'createRejetExpirePayment']);
        Route::post('/dossier-candidats/session-expires', [DossierCandidatController::class, 'createRejetExpire']);
        Route::post('/dossier-candidats/close', [DossierCandidatController::class, 'closeDossier']);
        Route::post('/dossier-candidats/open', [DossierCandidatController::class, 'openDossier']);
        Route::post('/dossier-candidats/externals', [DossierCandidatController::class, 'storeExternalReconduit']);
        Route::post('/candidat-session', [DossierCandidatController::class, 'updateSession']);
        Route::post('/candidat-sessions', [DossierCandidatController::class, 'updatePassedSession']);
        Route::post('/dossier-candidats', [DossierCandidatController::class, 'store']);

        Route::get('/candidat/dossier-session', [NpiANaTTController::class, 'getUserDossierSession']);

        Route::post('/generate-pdf', [GenerationController::class, 'generate']);

        Route::get('/candidat-payments', [CandidatPaymentController::class, 'index']);
        Route::post('/check-permis', [PermisNumPaymentController::class, 'checkPermis']);
        Route::get('/candidat-permis', [PermisNumPaymentController::class, 'getUserPermis']);
        Route::post('/permis-numeriques', [PermisNumPaymentController::class, 'store']);
        Route::get('/examens', [ExamenController::class, 'index']);


        Route::get('/dossier-candidats-parcours', [DossierCandidatController::class, 'getDossiersWithRelationsByCandidatId']);
        Route::get('/candidats-eservices-parcours', [DossierCandidatController::class, 'getEserviceByCandidatId']);
        Route::get('/dossier-candidats-souscription', [DossierCandidatController::class, 'getOneDossiersByCandidatId']);
        Route::match(['post', 'put'], '/dossier-candidats/{id}', [DossierCandidatController::class, 'update']);
        Route::match(['post', 'put'], '/update-dossier-candidats/{id}', [DossierCandidatController::class, 'updateCandidatDossier']);

        // Route::post('/candidat-parcours', [ParcoursCandidatController::class, 'store']);
        Route::post('/candidat-reconduits', [ReconduitController::class, 'store']);
        // Route::get('/candidats', [CandidatController::class, 'index']);
        // Route::post('/candidats-transfert', [CandidatController::class, 'candidatTransfert']);
        // Route::post('/candidats', [CandidatController::class, 'store']);
        // Route::match(['post', 'put'], '/candidats/{id}', [CandidatController::class, 'update']);
        // Route::delete('/candidats/{id}', [CandidatController::class, 'destroy']);

        // Route::get('/candidat-ecrit-notes', [CandidatEcritNoteController::class, 'index']);
        // Route::post('/candidat-ecrit-notes', [CandidatEcritNoteController::class, 'store']);
        // Route::get('/candidat-ecrit-notes/{id}', [CandidatEcritNoteController::class, 'show']);
        // Route::match(['post', 'put'], '/candidat-ecrit-notes/{id}', [CandidatEcritNoteController::class, 'update']);
        // Route::delete('/candidat-ecrit-notes/{id}', [CandidatEcritNoteController::class, 'destroy']);

        Route::post('/candidat-payments', [CandidatPaymentController::class, 'createTransaction']);
        // Route::post('/candidat-payments', [CandidatPaymentController::class, 'store']);
        Route::get('/candidat-payments/{id}', [CandidatPaymentController::class, 'show']);
        Route::match(['post', 'put'], '/candidat-payments/{id}', [CandidatPaymentController::class, 'update']);
        Route::delete('/candidat-payments/{id}', [CandidatPaymentController::class, 'destroy']);

        // Route::get('/candidat-responses', [CandidatReponseController::class, 'index']);
        // Route::post('/candidat-responses', [CandidatReponseController::class, 'store']);
        // Route::get('/candidat-responses/{id}', [CandidatReponseController::class, 'show']);
        // Route::match(['post', 'put'], '/candidat-responses/{id}', [CandidatReponseController::class, 'update']);
        // Route::delete('/candidat-responses/{id}', [CandidatReponseController::class, 'destroy']);

        // Route::get('/candidat-vagues', [CandidatVagueController::class, 'index']);
        // Route::post('/candidat-vagues', [CandidatVagueController::class, 'store']);
        // Route::get('/candidat-vagues/{id}', [CandidatVagueController::class, 'show']);
        // Route::match(['post', 'put'], '/candidat-vagues/{id}', [CandidatVagueController::class, 'update']);
        // Route::delete('/candidat-vagues/{id}', [CandidatVagueController::class, 'destroy']);

        Route::get('/dossier-motif-rejets', [DossierMotifRejetController::class, 'index']);
        Route::post('/dossier-motif-rejets', [DossierMotifRejetController::class, 'store']);
        Route::get('/dossier-motif-rejets/{id}', [DossierMotifRejetController::class, 'show']);
        Route::match(['post', 'put'], '/dossier-motif-rejets/{id}', [DossierMotifRejetController::class, 'update']);
        Route::delete('/dossier-motif-rejets/{id}', [DossierMotifRejetController::class, 'destroy']);


        // Route::get('/candidat-justif-absences', [CandidatJustifAbsenceController::class, 'index']);
        // Route::post('/candidat-justif-absences', [CandidatJustifAbsenceController::class, 'store']);
        // Route::get('/candidat-justif-absences/{id}', [CandidatJustifAbsenceController::class, 'show']);
        // Route::match(['post', 'put'], '/candidat-justif-absences/{id}', [CandidatJustifAbsenceController::class, 'update']);
        // Route::delete('/candidat-justif-absences/{id}', [CandidatJustifAbsenceController::class, 'destroy']);

        //eservice
        Route::get('eservices/get-transaction/{uuid}', [AuthenticiteController::class, 'checkTransactionUuid']);
        Route::get('get-transaction/{uuid}', [CandidatPaymentController::class, 'checkTransactionUuid']);
        Route::post('find-transactions', [FindPaymentController::class, 'procced']);

        Route::prefix('eservices')->group(function () {
            Route::prefix('authenticites')->group(function () {
                Route::post('/store', [AuthenticiteController::class, 'store']);
                Route::post('/payment', [AuthenticiteController::class, 'eservicePayment']);
                Route::get('/rejet/{id}', [AuthenticiteController::class, 'getAuthenticite']);
                Route::match(['post', 'put'], '/update/{id}', [AuthenticiteController::class, 'update']);
            });
            Route::prefix('attestation')->group(function () {
                Route::post('/store', [SuccesAttestationController::class, 'store']);
            });
            Route::prefix('permis-internationals')->group(function () {
                Route::post('/store', [PermisInternationalController::class, 'store']);
                Route::post('/payment', [PermisInternationalController::class, 'eservicePayment']);
                Route::get('/rejet/{id}', [PermisInternationalController::class, 'getPermisI']);
                Route::match(['post', 'put'], '/update/{id}', [PermisInternationalController::class, 'update']);
            });
            Route::prefix('duplicatas')->group(function () {
                Route::post('/store', [DuplicataController::class, 'store']);
                Route::post('/payment', [DuplicataController::class, 'eservicePayment']);
                Route::get('/rejet/{id}', [DuplicataController::class, 'getDuplicata']);
                Route::match(['post', 'put'], '/update/{id}', [DuplicataController::class, 'update']);
            });
            Route::prefix('echanges')->group(function () {
                Route::post('/store', [EchangeController::class, 'store']);
                Route::post('/payment', [EchangeController::class, 'eservicePayment']);
                Route::get('/rejet/{id}', [EchangeController::class, 'getEchange']);
                Route::match(['post', 'put'], '/update/{id}', [EchangeController::class, 'update']);
            });
            Route::prefix('prorogations')->group(function () {
                Route::post('/store', [ProrogationController::class, 'store']);
                Route::post('/payment', [ProrogationController::class, 'eservicePayment']);
                Route::get('/rejet/{id}', [ProrogationController::class, 'getProrogation']);
                Route::match(['post', 'put'], '/update/{id}', [ProrogationController::class, 'update']);
            });
        });

        Route::get('/api/documentation', function () {
            return view('vendor.l5-swagger.index');
        });
    });

    Route::prefix('webhooks')->group(function () {
        Route::post('fedapay', [WebHooksController::class, 'fedapay']);
    });
});
