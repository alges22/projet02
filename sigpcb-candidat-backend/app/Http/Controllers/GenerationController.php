<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\DossierSession;
use App\Models\ConvocationCode;
use App\Models\ConvocationConduite;
use App\Services\AttestationGenerator;
use App\Services\PermisGenerator;
use App\Services\ReceiptGenerator;
use App\Services\ConvocationGenerator;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;
use App\Services\ConvocationCteGenerator;
use App\Services\EserviceFactureGenerator;
use App\Services\FacturePNumeriqueGenerator;

class GenerationController extends ApiController
{
    protected $documentGenerators = [
        'receipt' => ReceiptGenerator::class,
        'convocation' => ConvocationGenerator::class,
    ];

    public function generate(string $type, Request $request)
    {
        if (!array_key_exists($type, $this->documentGenerators)) {
            return $this->errorResponse('Type de document introuvable', statuscode: 404);
        }

        $generatorClass = $this->documentGenerators[$type];
        $generator = new $generatorClass();

        try {
            $pdfContent = $generator->generate($request->all());
            if (!$pdfContent) {
                return $this->errorResponse('Échec de la génération du PDF');
            }

            // Vous pouvez personnaliser le nom du fichier PDF ici
            $filename = $type . '_' . now()->format('Ymd_His') . '.pdf';

            return Response::make($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename={$filename}",
            ]);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la génération du PDF');
        }
    }

    public function generateConvocation($encryptedDossierId)
    {
        try {
            // Séparer le npi et le dossierId
            list($Encodenpi, $EncodedossierId) = explode('-', $encryptedDossierId);
            // Décoder chaque donnée séparément
            $npi = base64_decode($Encodenpi);  // Décoder le NPI
            $dossierId = base64_decode($EncodedossierId);  // Décoder le DossierId
            // Vérifiez si le dossier de session existe pour le NPI et le dossierId
            $dossierSession = DossierSession::where('id', $dossierId)
                ->where('npi', $npi)
                ->first();

            if (!$dossierSession) {
                return $this->errorResponse("Le dossier de session n'a pas été trouvé ou ne correspond pas à ce NPI.", statuscode: 404);
            }


            // Vérifiez si un code existe déjà pour ce dossier de session
            $existingCode = ConvocationCode::where('dossier_session_id', $dossierId)->first();

            $dossierSession = DossierSession::where('id', $dossierId)
                ->where('npi', $npi)
                ->first();

            if (!$dossierSession) {
                // Gérer le cas où le dossier n'a pas été trouvé
                return $this->errorResponse("Le dossier de session n'a pas été trouvé ou ne correspond pas à ce NPI.", statuscode: 404);
            }

            if ($existingCode) {
                $generator = new ConvocationGenerator();
                // Vous pouvez définir les données nécessaires à la génération de la convocation ici
                $data = [
                    'token' => $dossierId,
                ];

                $pdfContent = $generator->generate($data);

                if (!$pdfContent) {
                    return $this->errorResponse('Échec de la génération du PDF');
                }

                // Retournez la convocation sous forme de réponse PDF
                return Response::make($pdfContent, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => "inline; filename=convocation.pdf",
                ]);
            }

            // Générez le contenu de la convocation en utilisant la classe ConvocationGenerator
            $generator = new ConvocationGenerator();

            do {
                $randomNumber = mt_rand(0, 999999999999);
                $otp_code = str_pad($randomNumber, 12, '0', STR_PAD_LEFT);
            } while (ConvocationCode::where('code', $otp_code)->exists());

            $convocationCode = ConvocationCode::where('dossier_session_id', $dossierId)->first();
            if (!$convocationCode) {
                // Insérer le code unique dans la table ConvocationCode
                $convocationCode = ConvocationCode::create([
                    'dossier_session_id' => $dossierId,
                    'code' => $otp_code,
                ]);
            } else {
                $convocationCode->update([
                    'code' => $otp_code,
                ]);
            }

            $data = [
                'token' => $dossierId,
            ];

            $pdfContent = $generator->generate($data);

            if (!$pdfContent) {
                return $this->errorResponse('Échec de la génération du PDF');
            }

            // Retournez la convocation sous forme de réponse PDF
            return Response::make($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=convocation.pdf",
            ]);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la génération de la convocation.');
        }
    }

    public function generateConduiteConvocation($encryptedDossierId)
    {
        try {
            list($Encodenpi, $EncodedossierId) = explode('-', $encryptedDossierId);
            // Décoder chaque donnée séparément
            $npi = base64_decode($Encodenpi);  // Décoder le NPI
            $dossierId = base64_decode($EncodedossierId);  // Décoder le DossierId
            // Vérifiez si le dossier de session existe pour le NPI et le dossierId
            $dossierSession = DossierSession::where('id', $dossierId)
                ->where('npi', $npi)
                ->first();

            if (!$dossierSession) {
                return $this->errorResponse("Le dossier de session n'a pas été trouvé ou ne correspond pas à ce NPI.", statuscode: 404);
            }

            // Vérifiez si un code existe déjà pour ce dossier de session
            $existingCode = ConvocationConduite::where('dossier_session_id', $dossierId)->first();

            // Récupérez le dossier de session en utilisant $dossierId
            $dossierSession = DossierSession::find($dossierId);

            if (!$dossierSession) {
                // Gérer le cas où le dossier n'a pas été trouvé
                return $this->errorResponse("Le dossier de session n'a pas été trouvé.", statuscode: 404);
            }
            if ($existingCode) {
                $generator = new ConvocationCteGenerator();
                // Vous pouvez définir les données nécessaires à la génération de la convocation ici
                $data = [
                    'token' => $dossierId,
                ];

                $pdfContent = $generator->generate($data);

                if (!$pdfContent) {
                    return $this->errorResponse('Échec de la génération du PDF');
                }

                // Retournez la convocation sous forme de réponse PDF
                return Response::make($pdfContent, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => "inline; filename=convocation.pdf",
                ]);
            }

            $generator = new ConvocationCteGenerator();

            do {
                $randomNumber = mt_rand(0, 999999999999);
                $otp_code = str_pad($randomNumber, 12, '0', STR_PAD_LEFT);
            } while (ConvocationConduite::where('code', $otp_code)->exists());

            $convocationConduite = ConvocationConduite::where('dossier_session_id', $dossierId)->first();
            if (!$convocationConduite) {
                // Insérer le code unique dans la table convocationConduite
                $convocationConduite = ConvocationConduite::create([
                    'dossier_session_id' => $dossierId,
                    'code' => $otp_code,
                ]);
            } else {
                $convocationConduite->update([
                    'code' => $otp_code,
                ]);
            }

            $data = [
                'token' => $dossierId,
            ];

            $pdfContent = $generator->generate($data);

            if (!$pdfContent) {
                return $this->errorResponse('Échec de la génération du PDF');
            }

            // Retournez la convocation sous forme de réponse PDF
            return Response::make($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=convocation.pdf",
            ]);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la génération de la convocation.');
        }
    }

    public function generateFacture($encryptedDossierId)
    {
        try {
            // Décryptez l'ID du dossier de session
            $dossierId = decrypt($encryptedDossierId);

            // Récupérez le dossier de session en utilisant $dossierId
            $dossierSession = DossierSession::find($dossierId);

            if (!$dossierSession) {
                // Gérer le cas où le dossier n'a pas été trouvé
                return $this->errorResponse("Le dossier de session n'a pas été trouvé.", statuscode: 404);
            }
            // Générez le contenu de la convocation en utilisant la classe ConvocationGenerator
            $generator = new ReceiptGenerator();
            $data = [
                'token' => $encryptedDossierId,
            ];

            $pdfContent = $generator->generate($data);

            if (!$pdfContent) {
                return $this->errorResponse('Échec de la génération du PDF');
            }

            // Retournez la convocation sous forme de réponse PDF
            return Response::make($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=facture.pdf",
            ]);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la génération de la facture.');
        }
    }

    public function generateNumFacture($encryptednpi)
    {
        try {
            // Décryptez l'ID du dossier de session
            $npi = decrypt($encryptednpi);

            // Récupérez le dossier de session en utilisant $dossierId
            $user = User::where('npi', $npi);

            if (!$user) {
                return $this->errorResponse("Le numéro npi n'a pas été trouvé.", statuscode: 404);
            }
            $generator = new FacturePNumeriqueGenerator();
            $data = [
                'token' => $encryptednpi,
            ];

            $pdfContent = $generator->generate($data);

            if (!$pdfContent) {
                return $this->errorResponse('Échec de la génération du PDF');
            }

            // Retournez la convocation sous forme de réponse PDF
            return Response::make($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=facture.pdf",
            ]);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la génération de la facture.');
        }
    }

    public function eserviceFacture($encryptednpi)
    {
        try {
            // Décryptez l'ID du dossier de session
            $npi = decrypt($encryptednpi);

            // Récupérez le dossier de session en utilisant $dossierId
            $user = User::where('npi', $npi);

            if (!$user) {
                return $this->errorResponse("Le numéro npi n'a pas été trouvé.", statuscode: 404);
            }
            $generator = new EserviceFactureGenerator();
            $data = [
                'token' => $encryptednpi,
            ];

            $pdfContent = $generator->generate($data);

            if (!$pdfContent) {
                return $this->errorResponse('Échec de la génération du PDF');
            }

            // Retournez la convocation sous forme de réponse PDF
            return Response::make($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=facture.pdf",
            ]);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la génération de la facture.');
        }
    }
    public function generatePermis($code)
    {
        try {

            try {
                $codePermis = decrypt($code);
            } catch (\Throwable $th) {
                abort(404, "Le lien est corrompu ou une erreur est survenue");
            }

            $codePermis = decrypt($code);

            $data = $this->importFromBase("permis/$codePermis");

            if (!$data) {
                # L'accession à cette méthode n'est pas via API, pour utiliser errorResponse
                abort(404, "Le permis que vous essayez de télécharger n'existe pas");
            }

            $permisGenetor = new PermisGenerator();

            $pdfContent = $permisGenetor->generate($data);
            // Retournez la convocation sous forme de réponse PDF
            return Response::make($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=permis.pdf",
            ]);
        } catch (\Throwable $e) {
            logger()->error($e);
            abort(500, 'Une erreur est survenue sur le serveur');
        }
    }

    public function generateAttestation($code)
    {
        try {

            try {
                $codePermis = decrypt($code);
            } catch (\Throwable $th) {
                abort(404, "Le lien est corrompu ou une erreur est survenue");
            }

            $codePermis = decrypt($code);

            $permisGenetor = new AttestationGenerator();
            $data['token'] = $code;

            $pdfContent = $permisGenetor->generate($data);
            // Retournez la convocation sous forme de réponse PDF
            return Response::make($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=attestation-permis.pdf",
            ]);
        } catch (\Throwable $e) {
            logger()->error($e);
            abort(500, 'Une erreur est survenue sur le serveur');
        }
    }
}
