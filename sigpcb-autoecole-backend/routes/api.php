<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DgiController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CountController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\LicenceController;
use App\Http\Controllers\CandidatController;
use App\Http\Controllers\NpiANaTTController;
use App\Http\Controllers\AuthCountController;
use App\Http\Controllers\AutoEcoleController;
use App\Http\Controllers\PromoteurController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\VerifyPhoneController;
use App\Http\Controllers\PdfGeneratorController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SuiviCandidatController;
use App\Http\Controllers\DemandeLicenceController;
use App\Http\Controllers\DossierSessionController;
use App\Http\Controllers\DemandeAgrementController;
use App\Http\Controllers\AutoEcoleTerritoireController;
use App\Http\Controllers\MoniteurRegistrationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WebHooksController;

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

Route::prefix('anatt-autoecole')->group(function () {
    Route::get('counts', CountController::class);
    Route::post('/npi-candidat', [NpiANaTTController::class, 'getNPI']);
    Route::post('/generate-phone-otp', [VerifyPhoneController::class, 'generate']);
    Route::post('/verify-phone', [VerifyPhoneController::class, 'verify']);

    Route::prefix('moniteurs')->group(function () {
        Route::post('verify', [MoniteurRegistrationController::class, 'verify']);
        Route::post('login', [MoniteurRegistrationController::class, 'login']);
        Route::post('resend-code', [MoniteurRegistrationController::class, 'resendCode']);
    });
    Route::prefix('/login')->group(function () {
        Route::post('/', [RegistrationController::class, 'login']);
        Route::post('/verify-otp', [RegistrationController::class, 'verifyOtp']);
        Route::post('/resend-code', [RegistrationController::class, 'resendCode']);
        Route::post('/verify-npi', [RegistrationController::class, 'verifyNpi']);
    });

    /**
     * Les routes à mettre sur base
     */
    Route::get('/chapitres-base', [BaseController::class, 'index']);
    Route::get('/langues-base', [BaseController::class, 'getLangue']);
    Route::get('/categorie-permis-base', [BaseController::class, 'getPermis']);
    Route::get('/communes', [BaseController::class, 'communes']);
    Route::get('/departements', [BaseController::class, 'departements']);

    # Demande agrément
    Route::post('/demande-agrements', [DemandeAgrementController::class, 'store']);
    Route::post('/demande-agrements/submit', [DemandeAgrementController::class, 'submitDemande']);


    Route::post('dossier-sessions/suivi-candidat/state', [DossierSessionController::class, 'updateStateSuiviCandidat']);

    Route::match(['post', 'put'], '/update-dossier-state', [SuiviCandidatController::class, 'updateState']);
    Route::get('/restrictions-admin', [AdminController::class, 'index']);
    Route::get('/fetch-data/{ifu}', [DgiController::class, 'fetchData']);
    Route::post('/verify-ifu', [DgiController::class, 'ifuVerified']);
    Route::post('/verify-ifu/{ifu}', [DgiController::class, 'verifyIfu']);
    Route::post('/resend-ifu-code', [DgiController::class, 'resendIfuVerificationCode']);

    Route::prefix('settings')->group(function () {
        Route::get("/", [SettingController::class, "index"]);
    });
    /**
     * Il faut être soit un promoteur soit un  promoteur pour accéder à ces routes
     */
    Route::middleware(['ae.users', 'ae.auth'])->group(function () {

        Route::get('faqs', [FaqController::class, 'index']);
        Route::get('monitoring-aes', [AutoEcoleController::class, 'monitoringAes']);

        Route::prefix('my-infos')->group(function () {
            Route::get('/', [AutoEcoleController::class, 'autoEcole']);
            Route::post('/', [AutoEcoleController::class, 'updateAutoEcole']);
            Route::get('/rejets/{rejetId}', [AutoEcoleController::class, 'rejets']);
            Route::post('/rejets/{rejetId}', [AutoEcoleController::class, 'updateRejets']);
        });
        Route::prefix('suivi-candidats')->group(function () {
            Route::post('/', [SuiviCandidatController::class, 'store']);
            Route::get('/', [SuiviCandidatController::class, 'index']);
        });
        Route::get('/historiques', HistoriqueController::class);
        Route::prefix('licences')->group(function () {
            Route::get('/', [LicenceController::class, 'index']);
            Route::prefix('demandes')->group(function () {
                Route::post('/', [DemandeLicenceController::class, 'store']);
                Route::get('/rejets/{demandeRejet}', [DemandeLicenceController::class, 'rejets']);
                Route::post('/rejets/{demandeRejet}', [DemandeLicenceController::class, 'update']);
            });
        });

        Route::prefix('agrements')->group(function () {
            Route::get('auto-ecoles', [PromoteurController::class, 'autoEcoles']);
        });
        Route::get('/demande-agrements/rejets/{demandeRejet}', [DemandeAgrementController::class, 'rejets']);
        Route::post('/demande-agrements/rejets/{demandeRejet}', [DemandeAgrementController::class, 'update']);

        Route::prefix('examens')->group(function () {
            Route::get('/', [ExamenController::class, 'index']);
        });

        Route::prefix('candidats')->group(function () {
            Route::get('/', [CandidatController::class, 'index']);
        });


        Route::get('auth-counts', AuthCountController::class);

        /**
         * Cette route  à travers les paramètres permet de gérer tous les cas avec les fichiers mêmes
         *
         */
        Route::prefix('dossier-sessions')->group(function () {
            Route::get('/', [DossierSessionController::class, 'index']);
        });

        Route::prefix('transactions')->group(function () {
            Route::post("/proceed/{transaction:uuid}", [TransactionController::class, "proceed"]);
            Route::post("/check-payment/{transaction:uuid}", [TransactionController::class, "checkPayment"]);
            Route::post("/{service}/{id}", [TransactionController::class, "store"]);
        });
        Route::get('/users/profiles', [AutoEcoleController::class, 'getProfil']);

        Route::prefix('inscription-candidats')->group(function () {
            Route::get('/', [InscriptionController::class, 'index']);
            Route::post('/', [InscriptionController::class, 'store']);
            Route::get('/{id}', [InscriptionController::class, 'show']);
            Route::put('/status/{id}', [InscriptionController::class, 'updateState']);
            Route::put('/{id}', [InscriptionController::class, 'update']);
            Route::delete('/{id}', [InscriptionController::class, 'destroy']);
        });

        Route::prefix('documents')->group(function () {
            #  Générer le HTML à imprimer
            Route::post('/{type}', [PdfGeneratorController::class, 'generate']);
        });
        Route::get('/api/documentation', function () {
            return view('vendor.l5-swagger.index');
        });
    });

    Route::prefix('webhooks')->group(function () {
        Route::post('fedapay', [WebHooksController::class, 'fedapay']);
    });
});
