<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Milon\Barcode\Facades\DNS1DFacade;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PermisGenerator
{

    public function generate(array $data)
    {
        $pdfData['logo'] = $this->url('logo.png');
        $pdfData['amoirie'] = $this->url('amoirie.jpg');

        $pdfData['codebar'] = $this->getBarcodePNG(env('APP_NAME'));
        $pdfData['codeqr'] = $this->getCodeQRPNG(route('generate-permis', ['permis' => $data['code_permis']]));
        $permis = $this->mapPermis($data['permis']);
        $pdfData['permis'] = $permis;
        $pdfData['permis_list'] = $this->permisList($permis);
        $pdfData['candidat'] = $data['candidat'];
        $pdfData['avatar'] = $data['candidat']['avatar']; //A prendre depuis ANIP
        $pdfData['code_permis'] = $data['code_permis'];
        $pdfData['delivered_at'] = $data['delivered_at'];

        # Signed_at
        $pdfData['signed_at'] = $data['signed_at'];
        # Singature
        $pdfData['signature'] = $this->url('signature.png');
        $pdfData['signataire'] = $data['signataire'];

        # Verification
        $pdfData['expired_at'] = $data['expired_at'];

        $pdfData['dossier'] = $data['dossier'];
        $content = view('pdf.permis-numerique', $pdfData)->render();

        return Pdf::loadHTML($content)
            ->setPaper('a4', 'landscape')
            ->output();
    }
    private function url(string $filename)
    {
        $name =  public_path('permis/' . $filename);
        if (file_exists($name)) {

            $extension = File::extension($name);
            if ($extension == 'svg') {
                $extension = "svg+xml";
            }
            $b64 = base64_encode(file_get_contents($name));
            return "data:image/$extension;base64,$b64";
        }
        return "";
    }


    private function mapPermis(array $permis)
    {
        return array_map(function ($p) {
            $p['icon'] = $this->url('icons/' . strtolower($p['name']) . '.png');

            return $p;
        }, $permis);
    }

    private function permisList($permis)
    {
        $permis = array_filter($permis, function ($p) {
            return $p['delivered_at'];
        });
        $mapped = array_map(function ($p) {
            return $p['name'];
        }, $permis);

        return implode(', ', $mapped);
    }

    /**
     *
     * @param string $barcode
     * @see https://github.com/milon/barcode
     */
    private function getBarcodePNG(string $barcode)
    {
        return "data:image/png;base64," . DNS1DFacade::getBarcodePNG($barcode, "C39+", 1, 32);
    }

    private function getCodeQRPNG(string $barcode)
    {
        return  "data:image/png;base64," . base64_encode(QrCode::size(60)->generate($barcode));
    }
}