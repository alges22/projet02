<?php
namespace Database\Seeders;

use App\Models\Candidat;
use App\Models\Recrutement;
use App\Models\ConvocationCode;
use Illuminate\Database\Seeder;

class CandidatSeeder extends Seeder
{
    public function run()
    {
        // Récupérer les recrutements
        $recrutements = Recrutement::all();

        // Créer un candidat pour chaque recrutement
        foreach ($recrutements as $recrutement) {
            if ($recrutement->entreprise_id == 1) {
                // Premier candidat avec npi '1111111111'
                $candidat1=Candidat::create([
                    'recrutement_id' => $recrutement->id,
                    'entreprise_id' => $recrutement->entreprise_id,
                    'langue_id' => '2',
                    'npi' => '1111111111',
                    'num_permis' => '25J58',
                    'permis_file' => 'ererer.png',
                    'state' => 'init',
                ]);
                do {
                    $randomNumber = mt_rand(0, 999999999999);
                    $otp_code = str_pad($randomNumber, 12, '0', STR_PAD_LEFT);
                } while (ConvocationCode::where('code', $otp_code)->exists());
    
                $convocationCode = ConvocationCode::create([
                    'candidat_id' => $candidat1->id,
                    'recrutement_id' => $recrutement->id,
                    'code' => $otp_code,
                ]);
            } elseif ($recrutement->entreprise_id == 2) {
                // Deuxième candidat avec npi '8888888888'
                $candidat2=Candidat::create([
                    'recrutement_id' => $recrutement->id,
                    'entreprise_id' => $recrutement->entreprise_id,
                    'langue_id' => '5',
                    'npi' => '8888888888',
                    'num_permis' => 'JUE85',
                    'permis_file' => 'erer.png',
                    'state' => 'init',
                ]);

                do {
                    $randomNumber = mt_rand(0, 999999999999);
                    $otp_code = str_pad($randomNumber, 12, '0', STR_PAD_LEFT);
                } while (ConvocationCode::where('code', $otp_code)->exists());
    
                $convocationCode = ConvocationCode::create([
                    'candidat_id' => $candidat2->id,
                    'recrutement_id' => $recrutement->id,
                    'code' => $otp_code,
                ]);
            }
        }
    }
}
