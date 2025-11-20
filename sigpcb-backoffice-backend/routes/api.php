<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CountController;
use App\Http\Controllers\JurieController;
use App\Http\Controllers\TitreController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\CatPermisTrancheAgeController;
use App\Http\Controllers\TrancheAgeController;
use App\Http\Controllers\CategoriePermisController;
use App\Http\Controllers\AppLogController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PermisController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\EchangeController;
use App\Http\Controllers\LicenceController;
use App\Http\Controllers\MentionController;
use App\Http\Controllers\NpiAnipController;
use App\Http\Controllers\ReponseController;
use App\Http\Controllers\CandidatController;
use App\Http\Controllers\LangueController;
use App\Http\Controllers\ChapitreController;
use App\Http\Controllers\ConduiteController;
use App\Http\Controllers\MoniteurController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResultatController;
use App\Http\Controllers\AutoEcoleController;
use App\Http\Controllers\CountBaseController;
use App\Http\Controllers\DuplicataController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\InspecteurController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SignataireController;
use App\Http\Controllers\UniteAdminController;
use App\Http\Controllers\AnnexeAnattController;
use App\Http\Controllers\ConvocationController;
use App\Http\Controllers\ExaminateurController;
use App\Http\Controllers\ProrogationController;
use App\Http\Controllers\RestrictionController;
use App\Http\Controllers\ActeSignableController;
use App\Http\Controllers\AuthenticiteController;
use App\Http\Controllers\BaseChapitreController;
use App\Http\Controllers\JuryCandidatController;
use App\Http\Controllers\PdfGeneratorController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ProgrammationController;
use App\Http\Controllers\QuestionVagueController;
use App\Http\Controllers\SuiviCandidatController;
use App\Http\Controllers\ValidationCedController;
use Spatie\Permission\Middlewares\RoleMiddleware;
use App\Http\Controllers\AnnexeAnattDepController;
use App\Http\Controllers\BaremeConduiteController;
use App\Http\Controllers\CandidatGrapheController;
use App\Http\Controllers\CodeInspectionController;
use App\Http\Controllers\DemandeLicenceController;
use App\Http\Controllers\AbsenceConduiteController;
use App\Http\Controllers\BaseAgregateursController;
use App\Http\Controllers\CandidatPaymentController;
use App\Http\Controllers\CodeExaminateurController;
use App\Http\Controllers\DemandeAgrementController;
use App\Http\Controllers\DemandeMoniteurController;
use App\Http\Controllers\EservicePaymentController;
use App\Http\Controllers\QuestionReponseController;
use App\Http\Controllers\AnnexeAnattJurieController;
use App\Http\Controllers\ConduiteInspectionController;
use App\Http\Controllers\DemandeExaminateurController;
use App\Http\Controllers\CandidatStatistiqueController;
use App\Http\Controllers\PermisInternationalController;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use App\Http\Controllers\CandidatConduiteReponseController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\CommuneController;
use App\Http\Controllers\ArrondissementController;
use App\Http\Controllers\JustificationAbsenceController;
use App\Http\Controllers\SubBaremeController;
use App\Http\Controllers\UpdateDossierSessionController;

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

Route::prefix("anatt-admin")->group(function () {


    /**
     * Login
     */
    Route::post('/login', [RegistrationController::class, 'login']);
    Route::post('/login/verify-otp', [RegistrationController::class, 'verifyOtp']);
    Route::post('/login/resend-code', [RegistrationController::class, 'resendCode']);

    Route::post('/login/forgot-password', [RegistrationController::class, 'forgotPassword']);
    Route::post('/login/reset-password', [RegistrationController::class, 'resetPassword']);
    Route::post('/login/update-password', [RegistrationController::class, 'updatePassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware(['auth:sanctum', \App\Http\Middleware\CheckUserActivity::class])->group(function () {
            Route::post('/candidat-session/update', [UpdateDossierSessionController::class, 'updateCandidatSession']);
            Route::post('/photo-by-candidats', [CandidatController::class, 'getImages']);

            //Justification absence
            Route::prefix('justification-absences')->group(function () {
                Route::get('/', [JustificationAbsenceController::class, 'index']);
                Route::put('/{id}', [JustificationAbsenceController::class, 'validateOrRejectJustif']);
            });

            //laisser passer
            Route::prefix('dispense-paiements')->group(function () {
                Route::post('/', [UpdateDossierSessionController::class, 'createDispensePaiement']);
                Route::put('/{id}', [UpdateDossierSessionController::class, 'validateOrRejectDispense']);
                Route::get('/', [UpdateDossierSessionController::class, 'index']);
            });
            Route::prefix('sub-baremes')->group(function () {
                Route::post('/', [SubBaremeController::class, 'store']); // Créer un sous-barème
                Route::put('/{id}', [SubBaremeController::class, 'update']); //mettre a jour un sous-barème
                Route::get('/{id}', [SubBaremeController::class, 'show']); // Afficher un sous-barème
                Route::delete('/{id}', [SubBaremeController::class, 'destroy']); // Supprimer un sous-barème
                Route::get('/bareme/{baremeId}', [SubBaremeController::class, 'getSubBaremesByBaremeId']); // Récupérer les sous-barèmes par ID de barème
            });

            Route::prefix('documents')->group(function () {
                #  Générer le HTML à imprimer
                Route::post('/{type}', [PdfGeneratorController::class, 'generate']);
            });
            Route::post('/salle-compo/multiple', [AnnexeAnattController::class, 'storeMultiple']);
            //Les routes de impersonation
            Route::prefix('impersonations')->group(function () {
                Route::post('/', [ImpersonationController::class, 'createImpersonation']);
            });

            Route::prefix('suivi-candidats')->group(function () {
                Route::get('/', [SuiviCandidatController::class, 'index']);

                Route::get('/rejets', [SuiviCandidatController::class, 'rejets']);

                // Route::post('/validate', [SuiviCandidatController::class, 'validateSuivi']);
                Route::post('/validate', [SuiviCandidatController::class, 'validateSuivi']);
                Route::post('/reject', [SuiviCandidatController::class, 'rejectSuivi']);
                Route::get('/{suivi_id}', [SuiviCandidatController::class, 'getCandidatSuivis']);
            });

            #Conduite reponse
            Route::prefix('candidat-conduite-reponses')->group(function () {
                Route::get('/{jury_candidat_id}', [CandidatConduiteReponseController::class, 'show']);
                Route::post('/', [CandidatConduiteReponseController::class, 'store']);
            });

            # Examen routes
            Route::prefix('examens')->group(function () {
                Route::get('/', [ExamenController::class, 'index']);
                Route::get('/all-sessions', [ExamenController::class, 'allSession']);
                Route::get('/recent', [ExamenController::class, 'recentExamen']);
                Route::get('/session-en-cours', [ExamenController::class, 'sessionEnCours']);
                Route::post('/', [ExamenController::class, 'store']);
                Route::get('/{id}', [ExamenController::class, 'show']);
                Route::put('/{id}', [ExamenController::class, 'update']);
                Route::delete('/{id}', [ExamenController::class, 'destroy']);
            });

            # Jury routes
            Route::prefix('jury')->group(function () {
                Route::get('/', [JurieController::class, 'index']);
            });

            # Jury routes
            Route::prefix('jury-candidats')->group(function () {
                Route::get('/{id}', [JuryCandidatController::class, 'show']);
            });

            Route::get('/candidats-permis/{candidatId}', [PermisController::class, 'getPermisByCandidatId']);
            Route::get('/candidat-permis/{candidatId}/{permisPrealableId}', [PermisController::class, 'checkPermisCombination']);

            Route::get('/annexeanatt-salle-compos/{annexe_anatt_id}', [AnnexeAnattController::class, 'getAnnexeAnattSalles']);

            # annexe-annats
            Route::prefix('annexe-annats')->group(function () {
                Route::post('/departements-couverts', [AnnexeAnattController::class, 'addDepartement']);
                Route::post('/', [AnnexeAnattController::class, 'store']);
                Route::get('/', [AnnexeAnattController::class, 'index']);
                Route::get('/{id}', [AnnexeAnattController::class, 'show'])->withoutMiddleware('auth:sanctum'); //Cette route devrait être ouverte
                Route::put('/{id}', [AnnexeAnattController::class, 'update']);
                Route::delete('/{id}', [AnnexeAnattController::class, 'destroy']);
                Route::post('/status',  [AnnexeAnattController::class, 'status']);
            });
            # Unite admin routes
            Route::prefix('agregateurs')->group(function () {
                Route::get('/', [BaseAgregateursController::class, 'index']);
                Route::post('/', [BaseAgregateursController::class, 'store']);
                Route::put('/{id}', [BaseAgregateursController::class, 'update']);
                Route::get('/{id}', [BaseAgregateursController::class, 'show']);
                Route::delete('/{id}', [BaseAgregateursController::class, 'destroy']);
                Route::post('/status',  [BaseAgregateursController::class, 'status']);
            });

            # Mention routes
            Route::prefix('mentions')->group(function () {
                Route::get('/', [MentionController::class, 'index']);
                Route::post('/', [MentionController::class, 'store']);
                Route::put('/{id}', [MentionController::class, 'update']);
                Route::get('/{id}', [MentionController::class, 'show']);
                Route::delete('/{id}', [MentionController::class, 'destroy']);
            });


            # Unite admin routes
            Route::prefix('base/chapitres')->group(function () {
                Route::get('/', [BaseChapitreController::class, 'index']);
                Route::post('/', [BaseChapitreController::class, 'store']);
                Route::put('/{id}', [BaseChapitreController::class, 'update']);
                Route::get('/{id}', [BaseChapitreController::class, 'show']);
                Route::delete('/{id}', [BaseChapitreController::class, 'destroy']);
                Route::post('/status',  [BaseChapitreController::class, 'status']);
            });

            Route::get('/langues', [LangueController::class, 'index']);
            Route::post('/langues/status', [LangueController::class, 'status']);
            Route::get('/langues/{id}', [LangueController::class, 'show']);
            Route::post('/langues', [LangueController::class, 'store']);
            Route::put('/langues/{id}', [LangueController::class, 'update']);
            Route::delete('/langues/{id}', [LangueController::class, 'destroy']);
            Route::put('/salle-compos/{id}', [CategoriePermisController::class, 'updateSalle']);
            Route::delete('/salle-compos/{id}', [CategoriePermisController::class, 'deleteSalle']);

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


            Route::get('/bareme-conduites', [BaremeConduiteController::class, 'index']);
            Route::get('/bareme-conduites/{id}', [BaremeConduiteController::class, 'show']);
            Route::post('/bareme-conduites', [BaremeConduiteController::class, 'store']);
            Route::post('/bareme-conduite', [BaremeConduiteController::class, 'addBareme']);
            Route::put('/bareme-conduites/{id}', [BaremeConduiteController::class, 'update']);
            Route::delete('/bareme-conduites/{id}', [BaremeConduiteController::class, 'destroy']);
            Route::prefix('bareme-conduites/categorie-permis')->group(function () {
                Route::get('/{categorie_permis_id}', [BaremeConduiteController::class, 'getBaremeConduiteByPermis']);
            });
            //Les routes de candidats
            Route::prefix('candidats')->group(function () {
                Route::get('/all', [CandidatController::class, 'index']);
                Route::get('/{dossier_id}/full-details', [CandidatController::class, 'fullDetails']);
                Route::get('historics/{npi}', [CandidatController::class, 'historics']);
            });
            #Licence auto ecole
            Route::prefix('licences')->group(function () {
                Route::get('/', [LicenceController::class, 'index']);
            });

            # Restrictions routes
            Route::prefix('restrictions')->group(function () {
                Route::get('/', [RestrictionController::class, 'index']);
                Route::post('/', [RestrictionController::class, 'store']);
                Route::put('/{id}', [RestrictionController::class, 'update']);
                Route::get('/{id}', [RestrictionController::class, 'show']);
                Route::delete('/{id}', [RestrictionController::class, 'destroy']);
            });

            Route::prefix('reporting')->group(function () {
                Route::get('/', [CandidatPaymentController::class, 'candidatsPayment']);
                Route::get('/eservices', [EservicePaymentController::class, 'index']);
            });
            //Generation de composition
            Route::prefix('programmations')->group(function () {
                Route::post('generate', [ProgrammationController::class, 'generate']);
                Route::post('distribute-into-salle', [ProgrammationController::class, 'distributeIntoSalle']);
                Route::get('statistiques', [ProgrammationController::class, 'statistiques']);
                Route::get('/', [ProgrammationController::class, 'programmations']);
            });

            //Generation de conduite
            Route::prefix('conduite')->group(function () {
                Route::get('resultat-code', [ConduiteController::class, 'resultatCode']);
                Route::get('programmations', [ConduiteController::class, 'programmations']);
                Route::post('jury-distribution', [ConduiteController::class, 'juryDitribution']);
                Route::post('generate', [ConduiteController::class, 'generate']);
            });
            #Agrement auto ecole
            Route::prefix('demande-agrement')->group(function () {
                Route::get('/', [DemandeAgrementController::class, 'index']);
                Route::post('/validate', [DemandeAgrementController::class, 'validateDemande']);
                Route::post('/rejet', [DemandeAgrementController::class, 'rejectDemande']);
            });

            Route::prefix('demande-licences')->group(function () {
                Route::get('/', [DemandeLicenceController::class, 'index']);
                Route::post('/validate', [DemandeLicenceController::class, 'validateDemande']);
                Route::post('/rejet', [DemandeLicenceController::class, 'rejectDemande']);
            });
            # Faqs routes
            Route::prefix('faqs')->group(function () {
                Route::get('/', [FaqController::class, 'index']);
                Route::post('/', [FaqController::class, 'store']);
                Route::put('/{id}', [FaqController::class, 'update']);
                Route::get('/{id}', [FaqController::class, 'show']);
                Route::delete('/{id}', [FaqController::class, 'destroy']);
            });
            #Auto ecole
            Route::prefix('auto-ecoles')->group(function () {
                Route::get('/', [AutoEcoleController::class, 'index']);
                Route::get('/raison-sociale', [AutoEcoleController::class, 'getRaisonSocial']);
                Route::post('/status', [AutoEcoleController::class, 'updateAEStatus']);
                Route::post('/create', [AutoEcoleController::class, 'createAE']);
                Route::put('/update/{id}', [AutoEcoleController::class, 'updateAE']);
                Route::put('/update-motineur/{id}', [AutoEcoleController::class, 'updateAEMoniteur']);
                Route::put('/update-promoteur/{id}', [AutoEcoleController::class, 'updateAEPromoteur']);
                Route::put('/update-vehicule/{id}', [AutoEcoleController::class, 'updateAEVehicule']);
            });

            # Unite admin routes
            Route::prefix('conduites')->group(function () {
                Route::post('/absences', [AbsenceConduiteController::class, 'makeAbsence']);
                Route::post('/signatures', [AbsenceConduiteController::class, 'candidatSignature']);
            });

            Route::prefix('importation-auto-ecoles')->group(function () {
                Route::post('/', [ImportController::class, 'autoEcoles']);
            });
            Route::prefix('importation-examinateurs')->group(function () {
                Route::post('/', [ImportController::class, 'examinateur']);
            });

            #Anip NPI
            Route::prefix('npi-informations')->group(function () {
                Route::post('/', [NpiAnipController::class, 'getNPI']);
            });

            //eservice
            Route::prefix('eservices')->group(function () {
                Route::prefix('authenticites')->group(function () {
                    Route::get('/', [AuthenticiteController::class, 'index']);
                    Route::post('/validate', [AuthenticiteController::class, 'validateDemande']);
                    Route::post('/rejet', [AuthenticiteController::class, 'rejectDemande']);
                });
                Route::prefix('permis-internationals')->group(function () {
                    Route::get('/', [PermisInternationalController::class, 'index']);
                    Route::post('/validate', [PermisInternationalController::class, 'validateDemande']);
                    Route::post('/rejet', [PermisInternationalController::class, 'rejectDemande']);
                });
                Route::prefix('duplicatas')->group(function () {
                    Route::get('/', [DuplicataController::class, 'index']);
                    Route::post('/validate', [DuplicataController::class, 'validateDemande']);
                    Route::post('/rejet', [DuplicataController::class, 'rejectDemande']);
                });
                Route::prefix('echanges')->group(function () {
                    Route::get('/', [EchangeController::class, 'index']);
                    Route::post('/validate', [EchangeController::class, 'validateDemande']);
                    Route::post('/rejet', [EchangeController::class, 'rejectDemande']);
                });
                Route::prefix('prorogations')->group(function () {
                    Route::get('/', [ProrogationController::class, 'index']);
                    Route::post('/validate', [ProrogationController::class, 'validateDemande']);
                    Route::post('/rejet', [ProrogationController::class, 'rejectDemande']);
                });
                Route::prefix('examinateurs')->group(function () {
                    Route::get('/', [DemandeExaminateurController::class, 'index']);
                    Route::post('/validate', [DemandeExaminateurController::class, 'validateDemande']);
                    Route::post('/rejet', [DemandeExaminateurController::class, 'rejectDemande']);
                });
                Route::prefix('moniteurs')->group(function () {
                    Route::get('/all', [MoniteurController::class, 'index']);
                    Route::get('/', [DemandeMoniteurController::class, 'index']);
                    Route::post('/validate', [DemandeMoniteurController::class, 'validateDemande']);
                    Route::post('/rejet', [DemandeMoniteurController::class, 'rejectDemande']);
                });
            });
            # permis
            Route::prefix('permis')->group(function () {
                Route::post('/', [PermisController::class, 'store']);
                Route::get('/', [PermisController::class, 'index']);
                Route::get('/{id}', [PermisController::class, 'show']);
                Route::put('/{id}', [PermisController::class, 'update']);
                Route::delete('/{id}', [PermisController::class, 'destroy']);
            });
            #modification de mot de passe
            Route::post('/login/password-update', [RegistrationController::class, 'passwordUpdate']);


            // Middleware de permission
            // Route::middleware([PermissionMiddleware::class . ':all'])->group(function () {

            Route::get('counts', CountController::class);
            Route::get('users/', [UserController::class, 'index']);
            Route::post('users/', [UserController::class, 'store']);
            Route::put('users/{id}', [UserController::class, 'update']);
            Route::delete('users/{id}', [UserController::class, 'destroy']);
            Route::get('titres/', [TitreController::class, 'index']);


            //Les routes de chapitres
            Route::prefix('chapitres')->group(function () {
                Route::get('/', [ChapitreController::class, 'index']);
                Route::get('/many', [ChapitreController::class, 'getMany']);
                Route::post('/', [ChapitreController::class, 'store']);
                Route::put('/{id}', [ChapitreController::class, 'update']);
                Route::get('/{id}', [ChapitreController::class, 'show']);
                Route::delete('/{id}', [ChapitreController::class, 'destroy']);
            });

            # Unite admin routes
            Route::prefix('unite-admins')->group(function () {
                Route::get('/', [UniteAdminController::class, 'index']);
                Route::post('/', [UniteAdminController::class, 'store']);
                Route::put('/{id}', [UniteAdminController::class, 'update']);
                Route::get('/{id}', [UniteAdminController::class, 'show']);
                Route::delete('/{id}', [UniteAdminController::class, 'destroy']);
                Route::post('/status',  [UniteAdminController::class, 'status']);
            });

            # Questions
            Route::prefix('questions')->group(function () {
                Route::controller(QuestionController::class)->group(function () {
                    Route::post('/', 'store');
                     Route::post('/status', 'toggleStatus');
                    Route::post('/audio', 'createQuestionLangue');
                    Route::post('/reponse', 'createReponse');
                    Route::get('/', 'index');
                    Route::get('/audios', 'getAudio');
                    Route::get('/distribute-questions', 'regenereQuestionCompo');
                    Route::get('/reponse/{id}', 'showQuestionReponse');
                    Route::get('/{id}', 'show');
                    Route::match(['POST', "PUT"], '/update-reponse/{id}', 'updateReponse');
                    Route::match(['POST', "PUT"], '/{id}', 'update');
                    Route::delete('/reponse/{id}', 'deleteReponse');
                    Route::delete('/audio/{id}', 'destroyAssignation');
                    Route::delete('/{id}', 'destroy');
                });
            });

            # Acte  signables routes
            Route::prefix('acte-signables')->group(function () {
                Route::get('/', [ActeSignableController::class, 'index']);
                Route::post('/', [ActeSignableController::class, 'store']);
                Route::post('/status',  [ActeSignableController::class, 'status']);
                Route::post('/assign-signataire', [ActeSignableController::class, 'assign']);
                Route::put('/{id}', [ActeSignableController::class, 'update']);
                Route::get('/{id}', [ActeSignableController::class, 'show']);
                Route::delete('/{id}', [ActeSignableController::class, 'destroy']);
            });
            # Users
            Route::prefix('users')->group(function () {
                Route::controller(UserController::class)->group(function () {
                    Route::post('deletes/', 'deletes');
                    Route::get('/profiles', 'getUser');
                    Route::get('/getall', 'getAll');
                    Route::get('/roles', 'getRole');
                    Route::get('/{id}', 'show');
                    Route::get('mail/{id}', 'sendMailByUserId');
                    Route::post('/deletes', 'deletes');
                    Route::post('/status', 'status');
                });
            });

            # Role routes
            Route::prefix('roles')->group(function () {
                Route::get('/', [RoleController::class, 'index']);
                Route::post('/', [RoleController::class, 'store']);
                Route::get('/{id}', [RoleController::class, 'show']);
                Route::put('/{id}', [RoleController::class, 'update']);
                Route::delete('/{id}', [RoleController::class, 'destroy']);
            });


            # AnnexeAnattJurie routes
            Route::prefix('annexe-jury')->group(function () {
                Route::get('/', [AnnexeAnattJurieController::class, 'index']);
                Route::get('/juries-annexeanatt/{id}', [AnnexeAnattJurieController::class, 'getAnnexeJury']);
                Route::post('/', [AnnexeAnattJurieController::class, 'store']);
                Route::get('/{id}', [AnnexeAnattJurieController::class, 'show']);
                Route::put('/{id}', [AnnexeAnattJurieController::class, 'update']);
                Route::delete('/{id}', [AnnexeAnattJurieController::class, 'destroy']);
            });

            # Permission routes
            Route::prefix('permissions')->group(function () {
                Route::get('/', [PermissionController::class, 'index']);
            });

            # Inspecteurs
            Route::get('/salles-inspecteurs/{salle_compo_id}', [InspecteurController::class, 'inspecteursBySalle']);
            Route::get('/inspecteurs-annexeanatt/{annexe_id}', [InspecteurController::class, 'inspecteursByAnnexe']);
            Route::get('/annexe-anatts/{$id}/salles-inspecteurs', [InspecteurController::class, 'getAssignation']);
            Route::post('/salles-inspecteurs', [InspecteurController::class, 'inspecteursBySalleAndSession']);
            Route::prefix('inspecteurs')->group(function () {
                Route::controller(InspecteurController::class)->group(function () {
                    Route::post('/', 'store');
                    Route::post('/assign', 'assignInspecteur');
                    Route::get('/', 'index');
                    Route::get('/{id}', 'show');
                    Route::put('/{id}', 'update');
                    Route::delete('/{id}', 'destroy');
                });
            });

            # Examinateurs
            Route::get('/examinateurs-annexeanatt/{annexe_id}', [ExaminateurController::class, 'examinateursByAnnexe']);
            Route::prefix('examinateurs')->group(function () {
                Route::controller(ExaminateurController::class)->group(function () {
                    Route::post('/', 'store');
                    Route::post('/assign', 'assignInspecteur');
                    Route::get('/', 'index');
                    Route::get('/{id}', 'show');
                    Route::put('/{id}', 'update');
                    Route::delete('/{id}', 'destroy');
                });
            });
            Route::get('/logs', [AppLogController::class, 'index']);

            # Entreprise
            Route::prefix('entreprises')->group(function () {
                Route::controller(EntrepriseController::class)->group(function () {
                    Route::get('/', 'index');
                    Route::get('/annexe-recrutements/{id}', 'getSessionByAnnexe');
                    Route::get('/get-recrutements', 'getSession');
                    Route::get('/recrutements/{id}', 'getRecrutement');
                    Route::get('/show-epreuve/{id}', 'showEpreuve');
                    Route::get('/get-conduite-epreuves/{recrutement_id}', 'getEpreuvesByRecrutementId');
                    Route::get('/recrutement/{id}/candidats', 'candidatByRecrutement');
                    Route::get('/resultats/{id}', 'resultatByRecrutement');
                    Route::get('/session/{id}/candidats', 'candidatsByRecrutement');
                    Route::put('/send-convocations/{id}', 'sendConvocations');
                    Route::put('/end-compo/{id}', 'compoEnd');
                    Route::put('/send-resultats/{id}', 'sendResultat');
                    Route::put('/update-conduite-epreuve/{id}', 'updateEpreuve');
                    Route::put('/{id}', 'update');
                    Route::post('/', 'store');
                    Route::delete('/{id}', 'destroy');
                    Route::delete('/delete-conduite-epreuve/{id}', 'destroyEpreuve');
                    Route::post('/validate', 'validateDemande');
                    Route::post('/rejet', 'rejectDemande');
                    Route::post('/start-compo', 'startCompo');
                    Route::post('/add-comduite-epreuves', 'storeConduiteEpreuve');
                    Route::post('/candidat-conduite-notes', 'candidatConduiteNote');
                });
            });


            // # Question reponses
            // Route::prefix('question-reponses')->group(function () {
            //     Route::controller(QuestionReponseController::class)->group(function () {
            //         Route::post('/', 'store');
            //         Route::get('/', 'index');
            //         Route::get('/{id}', 'show');
            //         Route::put('/{id}', 'update');
            //         Route::delete('/{id}', 'destroy');
            //     });
            // });


            # Question vagues php artisan
            Route::prefix('question-vagues')->group(function () {
                Route::controller(QuestionVagueController::class)->group(function () {
                    Route::post('/', 'store');
                    Route::get('/{questionVague}', 'questions');
                });
            });

            # Reponses
            Route::prefix('reponses')->group(function () {
                Route::controller(ReponseController::class)->group(function () {
                    Route::post('/', 'store');
                    Route::get('/', 'index');
                    Route::get('/{id}', 'show');
                    Route::put('/{id}', 'update');
                    Route::delete('/{id}', 'destroy');
                });
            });

            # Signataires
            Route::prefix('signataires')->group(function () {
                Route::controller(SignataireController::class)->group(function () {
                    Route::post('/', 'store');
                    Route::get('/', 'index');
                    Route::get('/{id}', 'show');
                    Route::put('/{id}', 'update');
                    Route::post('assign-acte', 'assignActe');
                    Route::delete('/{id}', 'destroy');
                });
            });

            # Titres routes
            Route::prefix('titres')->group(function () {
                Route::post('/', [TitreController::class, 'store']);
                Route::post('/status', [TitreController::class, 'status']);
                Route::put('/{id}', [TitreController::class, 'update']);
                Route::get('/{id}', [TitreController::class, 'show']);
                Route::delete('/{id}', [TitreController::class, 'destroy']);
            });

            Route::prefix('validation-ced')->group(function () {
                Route::get('/', [ValidationCedController::class, 'index']);
                Route::post('/validation', [ValidationCedController::class, 'validateJustif']);
            });

            Route::prefix('code-inspections')->group(function () {
                Route::get("recapts", [CodeInspectionController::class, 'recapts']);
                Route::get("agendas", [CodeInspectionController::class, 'agendas']);
                Route::get("vagues", [CodeInspectionController::class, 'vagues']); //list des vagues
                Route::get("vagues/{vague_id}/candidats", [CodeInspectionController::class, 'candidats']); //list des vagues
                Route::post('mark-as-abscent', [CodeInspectionController::class, 'markAsAbscent']);
                Route::post('open-session', [CodeInspectionController::class, 'openSession']);
                Route::post('stop-candidat-compo', [CodeInspectionController::class, 'stopCandidatCompo']);
                Route::post('start-compo', [CodeInspectionController::class, 'startCompo']);
                Route::post('reset-compo', [CodeInspectionController::class, 'resetCompo']);
                Route::post('emarges', [CodeInspectionController::class, 'emarges']);
                Route::post('pause', [CodeInspectionController::class, 'pause']);
                Route::get('salles', [CodeInspectionController::class, 'salles']);
                Route::get('verify-candidat', [CodeInspectionController::class, 'verifyCandidat']);
            });


            # Conduite Inspection
            Route::prefix('conduite-inspections')->group(function () {
                Route::controller(ConduiteInspectionController::class)->group(function () {
                    Route::post("dossier-jury", [CodeExaminateurController::class, 'getDossierbyJury']);
                    Route::post("dossier-noter", [CodeExaminateurController::class, 'getNotedDossierbyJury']);
                    Route::post("recapts", [CodeExaminateurController::class, 'recapts']);
                    Route::post("agendas", [CodeExaminateurController::class, 'agendas']);
                    Route::post("vagues", [CodeExaminateurController::class, 'vague']);
                    Route::post("stop-compo", [CodeExaminateurController::class, 'closeJury']);
                    Route::get("verify-candidat", [CodeExaminateurController::class, 'verifyCandidat']);
                    Route::post('/', 'store');
                    Route::get("examinateur-jury/{examen_id}", [CodeExaminateurController::class, 'getExaminateurJury']);
                    Route::get('/', 'index');
                    Route::get('/{id}', 'show');
                    Route::put('/{id}', 'update');
                    Route::delete('/{id}', 'destroy');
                });
            });

            Route::prefix('resultats')->group(function () {
                Route::get('/', [ResultatController::class, 'resultats']);
                Route::get('conduites', [ResultatController::class, 'conduites']);
                Route::get('statistic-code', [ResultatController::class, 'statisticCode']);
                Route::get('statistic-conduite', [ResultatController::class, 'statisticConduite']);
                Route::get('admis', [ResultatController::class, 'admis']);
                Route::get('recales', [ResultatController::class, 'recales']);
                Route::get('list-emargement', [ResultatController::class, 'listEmargement']);
                Route::get('admis-permis', [ResultatController::class, 'admisPermis']);
            });

            Route::prefix('charts')->group(function () {
                Route::get('candidats', CandidatGrapheController::class);
            });
            Route::prefix('statistics')->group(function () {
                Route::get('candidats', [CandidatStatistiqueController::class, 'index']);
            });

            Route::post('convocations', [ConvocationController::class, 'sendConvocations']);
            Route::post('conduite/convocations', [ConvocationController::class, 'sendConduiteConvocations']);
            // });
            Route::prefix('configs')->group(function () {
                Route::post('question-to-compose', [ConfigController::class, 'setQuestionToCompose']);
                Route::get('/', [ConfigController::class, 'index']);
            });
        });
    });
});
