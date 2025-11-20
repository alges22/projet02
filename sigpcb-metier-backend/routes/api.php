<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CountController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\EchangeController;
use App\Http\Controllers\CandidatController;
use App\Http\Controllers\NpiANaTTController;
use App\Http\Controllers\AutoEcoleController;
use App\Http\Controllers\DuplicataController;
use App\Http\Controllers\GenerationController;
use App\Http\Controllers\ProrogationController;
use App\Http\Controllers\RecrutementController;
use App\Http\Controllers\RestrictionController;
use App\Http\Controllers\AuthenticiteController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\CandidatVagueController;
use App\Http\Controllers\ParcoursSuiviController;
use App\Http\Controllers\DossierSessionController;
use App\Http\Controllers\CandidatPaymentController;
use App\Http\Controllers\CandidatReponseController;
use App\Http\Controllers\DemandeMoniteurController;
use App\Http\Controllers\EntrepriseLoginController;
use App\Http\Controllers\ParcoursCandidatController;
use App\Http\Controllers\PermisNumPaymentController;
use App\Http\Controllers\CandidatEcritNoteController;
use App\Http\Controllers\DossierMotifRejetController;
use App\Http\Controllers\DemandeExaminateurController;
use App\Http\Controllers\MoniteurRegistrationController;
use App\Http\Controllers\CandidatJustifAbsenceController;
use App\Http\Controllers\AnnexeAnattController;
use App\Http\Controllers\DossierCandidatController;
use App\Http\Controllers\EserviceParcourController;

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



Route::prefix('anatt-examinateur')->group(function () {

    Route::post('/login', [RegistrationController::class, 'login']);
    Route::post('/register', [RegistrationController::class, 'register']);
    Route::post('/verify-otp', [RegistrationController::class, 'verifyOtp']);
    Route::post('/resend-code', [RegistrationController::class, 'resendCode']);
    Route::post('/verify-npi', [RegistrationController::class, 'verifyNpi']);
    Route::post('/npi-candidat', [NpiANaTTController::class, 'getNPI']);
    // Route::get('/candidats/{id}', [CandidatController::class, 'show']);
    // Route::get('/candidats/npi/{npi}', [CandidatController::class, 'showByNpi']);
    // Route::get('/users', [UserController::class, 'index']);

    //eservice
    Route::prefix('eservices')->group(function () {
        Route::prefix('examinateurs')->group(function () {
            Route::post('/store', [DemandeExaminateurController::class, 'store']);
        });
    });
        # annexe-annats
        Route::prefix('annexe-anatts')->group(function () {
            Route::get('/', [AnnexeAnattController::class, 'index']);
            Route::get('/{id}', [AnnexeAnattController::class, 'show']);
        });
    # Unite admin routes
    Route::prefix('agregateurs-base')->group(function () {
        Route::get('/', [BaseController::class, 'index']);
    });
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
    Route::prefix('autoecoles')->group(function () {
        Route::get('/', [AutoEcoleController::class, 'index']);
    });
    // Route::prefix('parcours-suivis')->group(function () {
    //     Route::get('/', [ParcoursSuiviController::class, 'index']);
    //     Route::post('/convocation-codes', [ParcoursSuiviController::class, 'storeConvocationCode']);
    //     Route::post('/convocation-conduites', [ParcoursSuiviController::class, 'storeConvocationConduite']);
    //     Route::post('/resultats-codes', [ParcoursSuiviController::class, 'resultatCode']);
    //     Route::post('/absences', [ParcoursSuiviController::class, 'storeJustif']);
    //     Route::get('/all', [ParcoursSuiviController::class, 'getAll']);
    //     Route::post('/', [ParcoursSuiviController::class, 'store']);
    //     Route::get('/{candidat_id}', [ParcoursSuiviController::class, 'show']);
    // });
    // Route::get('/dossier-candidat/{dossier_id}/full', [DossierCandidatController::class, 'fullDossier']);
    // Route::get('/dossier-candidats', [DossierCandidatController::class, 'index']);
    // Route::get('/dossier-session/{dossier_session_id}', [DossierCandidatController::class, 'getDossierSessionInformation']);
    // Route::match(['post', 'put'], '/dossier-session/{dossier_session_id}', [DossierCandidatController::class, 'updateDossierSession']);
    // Route::get('/dossier-candidats-souscriptions/{id}', [DossierCandidatController::class, 'getDossiersByCandidatId']);
    // Route::get('/dossier-candidats-byautoecole/{id}', [DossierCandidatController::class, 'getDossiersByAutoEcoleId']);
    // Route::post('/check-permis-prealable', [DossierCandidatController::class, 'checkPermisPrealable']);
    // Route::get('/dossier-candidats/{id}', [DossierCandidatController::class, 'show']);
    // Route::delete('/dossier-candidats/{id}', [DossierCandidatController::class, 'destroy']);

    // Route::match(['post', 'put'], '/update-dossier-state', [DossierCandidatController::class, 'updateState']);
    // Route::match(['post', 'put'], '/updat-dossier-state', [DossierCandidatController::class, 'updateStateCed']);

    // Route::get('/init-dossier-candidats-byautoecole/{id}', [DossierCandidatController::class, 'getInitDossiersByAutoEcoleId']);
    // Route::get('/pending-dossier-candidats-byautoecole/{id}', [DossierCandidatController::class, 'getPendingDossiersByAutoEcoleId']);
    // Route::get('/validate-dossier-candidats-byautoecole/{id}', [DossierCandidatController::class, 'getValidateDossiersByAutoEcoleId']);
    // Route::get('/rejet-dossier-candidats-byautoecole/{id}', [DossierCandidatController::class, 'getRejetDossiersByAutoEcoleId']);
    Route::get('counts', CountController::class);
    /**
     * Les routes des auto-écoles
     */
    // Route::prefix('auto-ecoles/{id}')->group(function () {
    //     //Les candidats liés
    //     Route::prefix('dossiers-candidats')->group(function () {
    //         Route::get('/inits', [DossierCandidatController::class, 'getInitDossiersByAutoEcoleId']);
    //     });

    //     Route::prefix("dossier-sessions")->group(function () {
    //         Route::get('/inits', [DossierSessionController::class, 'getInitSessionByAutoEcoleId']);
    //     });
    // });
    // Route::prefix('dossier-sessions')->group(function () {
    //     Route::get('/', [DossierSessionController::class, 'index']);

    //     Route::prefix('{id}')->group(function () {
    //         Route::get('/', [DossierSessionController::class, 'show']);
    //         Route::get('/full-details', [DossierSessionController::class, 'fullDetails']);
    //     });
    // });


    Route::middleware(['user.auth'])->group(function () {
        Route::get('/candidat-parcours', [ParcoursCandidatController::class, 'index']);

        // Route::post('/dossier-candidats/justification-paiement', [DossierCandidatController::class, 'closeAndCreateDossierSession']);
        // Route::post('/dossier-candidats/expire-paiement', [DossierCandidatController::class, 'createRejetExpirePayment']);
        // Route::post('/dossier-candidats/close', [DossierCandidatController::class, 'closeDossier']);
        // Route::post('/dossier-candidats/open', [DossierCandidatController::class, 'openDossier']);
        // Route::post('/candidat-session', [DossierCandidatController::class, 'updateSession']);
        // Route::post('/dossier-candidats', [DossierCandidatController::class, 'store']);

        // Route::get('/candidat/dossier-session', [NpiANaTTController::class, 'getUserDossierSession']);

        Route::post('/generate-pdf', [GenerationController::class, 'generate']);

        // Route::get('/candidat-payments', [CandidatPaymentController::class, 'index']);
        Route::post('/check-permis', [PermisNumPaymentController::class, 'checkPermis']);
        Route::get('/candidat-permis', [PermisNumPaymentController::class, 'getUserPermis']);
        Route::post('/permis-numeriques', [PermisNumPaymentController::class, 'store']);
        Route::get('/examens', [ExamenController::class, 'index']);


        // Route::get('/dossier-candidats-parcours', [DossierCandidatController::class, 'getDossiersWithRelationsByCandidatId']);
        Route::get('/examinateurs-eservices-parcours', [EserviceParcourController::class, 'getEserviceByCandidatId']);
        // Route::get('/dossier-candidats-souscription', [DossierCandidatController::class, 'getOneDossiersByCandidatId']);
        // Route::match(['post', 'put'], '/dossier-candidats/{id}', [DossierCandidatController::class, 'update']);

        Route::post('/candidat-parcours', [ParcoursCandidatController::class, 'store']);
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

        // Route::post('/candidat-payments', [CandidatPaymentController::class, 'store']);
        // Route::get('/candidat-payments/{id}', [CandidatPaymentController::class, 'show']);
        // Route::match(['post', 'put'], '/candidat-payments/{id}', [CandidatPaymentController::class, 'update']);
        // Route::delete('/candidat-payments/{id}', [CandidatPaymentController::class, 'destroy']);

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

        // Route::get('/dossier-motif-rejets', [DossierMotifRejetController::class, 'index']);
        // Route::post('/dossier-motif-rejets', [DossierMotifRejetController::class, 'store']);
        // Route::get('/dossier-motif-rejets/{id}', [DossierMotifRejetController::class, 'show']);
        // Route::match(['post', 'put'], '/dossier-motif-rejets/{id}', [DossierMotifRejetController::class, 'update']);
        // Route::delete('/dossier-motif-rejets/{id}', [DossierMotifRejetController::class, 'destroy']);


        // Route::get('/candidat-justif-absences', [CandidatJustifAbsenceController::class, 'index']);
        // Route::post('/candidat-justif-absences', [CandidatJustifAbsenceController::class, 'store']);
        // Route::get('/candidat-justif-absences/{id}', [CandidatJustifAbsenceController::class, 'show']);
        // Route::match(['post', 'put'], '/candidat-justif-absences/{id}', [CandidatJustifAbsenceController::class, 'update']);
        // Route::delete('/candidat-justif-absences/{id}', [CandidatJustifAbsenceController::class, 'destroy']);

        //eservice
        Route::prefix('eservices')->group(function () {

            Route::prefix('examinateurs')->group(function () {
                Route::get('/rejet/{id}', [DemandeExaminateurController::class, 'getDemande']);
                Route::match(['post', 'put'],'/update/{id}', [DemandeExaminateurController::class, 'update']);
            });
        });

        Route::get('/api/documentation', function () {
            return view('vendor.l5-swagger.index');
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
    });
});



Route::prefix('anatt-moniteur')->group(function () {

    Route::post('/login', [MoniteurRegistrationController::class, 'login']);
    Route::post('/register', [MoniteurRegistrationController::class, 'register']);
    Route::post('/verify-otp', [MoniteurRegistrationController::class, 'verifyOtp']);
    Route::post('/resend-code', [MoniteurRegistrationController::class, 'resendCode']);
    Route::post('/verify-npi', [MoniteurRegistrationController::class, 'verifyNpi']);
    Route::post('/npi-candidat', [NpiANaTTController::class, 'getNPI']);
    // Route::get('/candidats/{id}', [CandidatController::class, 'show']);
    // Route::get('/candidats/npi/{npi}', [CandidatController::class, 'showByNpi']);
    // Route::get('/users', [UserController::class, 'index']);

    //eservice
    Route::prefix('eservices')->group(function () {
        Route::prefix('moniteurs')->group(function () {
            Route::post('/store', [DemandeMoniteurController::class, 'store']);
        });
    });
    Route::get('/moniteur-parcours', [RecrutementController::class, 'getParcourForEntreprise']);
    Route::middleware(['moniteur.auth'])->group(function () {
        Route::prefix('eservices')->group(function () {
            Route::prefix('moniteurs')->group(function () {
                Route::get('/rejet/{id}', [DemandeMoniteurController::class, 'getDemande']);
                Route::match(['post', 'put'],'/update/{id}', [DemandeMoniteurController::class, 'update']);
            });
        });
        Route::get('/moniteurs-eservices-parcours', [DemandeMoniteurController::class, 'getEserviceByCandidatId']);

    });

    Route::middleware('auth:sanctum')->group(function () {
    });
});



Route::prefix('anatt-entreprise')->group(function () {

    Route::post('/login', [EntrepriseLoginController::class, 'login']);
    Route::post('/register', [EntrepriseLoginController::class, 'register']);
    Route::post('/verify-otp', [EntrepriseLoginController::class, 'verifyOtp']);
    Route::post('/resend-code', [EntrepriseLoginController::class, 'resendCode']);
    Route::post('/verify-npi', [EntrepriseLoginController::class, 'verifyNpi']);
    Route::post('/npi-candidat', [NpiANaTTController::class, 'getNPI']);
    // Route::get('/users', [UserController::class, 'index']);
    // Route::get('/candidats/{id}', [CandidatController::class, 'show']);
    // Route::get('/candidats/npi/{npi}', [CandidatController::class, 'showByNpi']);

    # Unite admin routes
    Route::prefix('agregateurs-base')->group(function () {
        Route::get('/', [BaseController::class, 'index']);
    });
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
        Route::post('/convocation-codes', [ParcoursSuiviController::class, 'storeConvocationCode']);
        Route::post('/convocation-conduites', [ParcoursSuiviController::class, 'storeConvocationConduite']);
        Route::post('/resultats-codes', [ParcoursSuiviController::class, 'resultatCode']);
        Route::post('/absences', [ParcoursSuiviController::class, 'storeJustif']);
        // Route::get('/all', [ParcoursSuiviController::class, 'getAll']);
        Route::post('/', [ParcoursSuiviController::class, 'store']);
        Route::get('/{candidat_id}', [ParcoursSuiviController::class, 'show']);
    });




    Route::middleware(['entreprise.auth'])->group(function () {
        Route::get('counts', CountController::class);
        // Route::get('/candidat-parcours', [ParcoursCandidatController::class, 'index']);
        Route::prefix('recrutements')->group(function () {
            Route::get('/entreprise-parcours', [RecrutementController::class, 'getParcourForEntreprise']);
            Route::get('/', [RecrutementController::class, 'index']);
            Route::post('/', [RecrutementController::class, 'store']);
            Route::post('/add-candidat', [RecrutementController::class, 'storeCandidat']);
            Route::get('/session-candidats/{id}', [RecrutementController::class, 'candidatByRecrutement']);
            Route::get('/show-candidat/{id}', [RecrutementController::class, 'showCandidat']);
            Route::get('/get-rejet/{id}', [RecrutementController::class, 'getMotif']);
            Route::get('/{id}', [RecrutementController::class, 'show']);
            Route::match(['post', 'put'], '/close/{id}', [RecrutementController::class, 'sendSession']);
            Route::match(['post', 'put'], '/{id}', [RecrutementController::class, 'update']);
            Route::match(['post', 'put'], '/update-candidat/{id}', [RecrutementController::class, 'updateCandidat']);
            Route::delete('/delete-candidat/{id}', [RecrutementController::class, 'deleteCandidat']);
            Route::delete('/{id}', [RecrutementController::class, 'destroy']);
            Route::match(['post', 'put'],'/update-session/{id}', [RecrutementController::class, 'updateRecrutementRejet']);
        });

        Route::post('/generate-pdf', [GenerationController::class, 'generate']);
        Route::post('/candidat-parcours', [ParcoursCandidatController::class, 'store']);
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

        // Route::get('/dossier-motif-rejets', [DossierMotifRejetController::class, 'index']);
        // Route::post('/dossier-motif-rejets', [DossierMotifRejetController::class, 'store']);
        // Route::get('/dossier-motif-rejets/{id}', [DossierMotifRejetController::class, 'show']);
        // Route::match(['post', 'put'], '/dossier-motif-rejets/{id}', [DossierMotifRejetController::class, 'update']);
        // Route::delete('/dossier-motif-rejets/{id}', [DossierMotifRejetController::class, 'destroy']);





        Route::get('/api/documentation', function () {
            return view('vendor.l5-swagger.index');
        });
    });
});
