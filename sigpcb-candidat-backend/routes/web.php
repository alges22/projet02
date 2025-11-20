<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenerationController;
use App\Http\Controllers\SuccesAttestationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/generate-convocation/{encryptedDossierId}', [GenerationController::class, 'generateConvocation'])->name('generate-convocation');
Route::get('/generate-conduite-convocation/{encryptedDossierId}', [GenerationController::class, 'generateConduiteConvocation'])->name('generate-conduite-convocation');
Route::get('/generate-facture/{encryptedDossierId}', [GenerationController::class, 'generateFacture'])->name('generate-facture');
Route::get('/eservice-facture/{encryptednpi}', [GenerationController::class, 'eserviceFacture'])->name('eservice-facture');
Route::get('/generate-numpermis-facture/{encryptednpi}', [GenerationController::class, 'generateNumFacture'])->name('generate-numpermis-facture');

Route::get('/generate-permis/{permis}', [GenerationController::class, 'generatePermis'])->name('generate-permis');
Route::get('/generate-attestation/{permis}', [GenerationController::class, 'generateAttestation'])->name('generate-attestation');
Route::get('/verify-permit/{code}', [SuccesAttestationController::class, 'verifyPermit'])->name('verify.permit');

