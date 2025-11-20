<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//FR" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title>SIGPCB</title>

    <style type="text/css">
        body {
            padding: 1rem 2rem;
            font-family: Montserrat, sans-serif;
            font-size: 12px;
        }

        h1,
        h2 {
            font-size: 14px;
        }

        ul {
            padding: 0;
        }

        ul li {
            list-style-type: none;
            margin-top: 0.5rem;
        }

        ul li strong {
            text-decoration: underline;
        }

        .title {
            color: #515151;
        }

        .header {
            margin-bottom: 2rem;
        }

        .header ul li {
            margin-top: 2px;
        }

        .header>*>* {
            display: inline-block;
        }

        .header .top {
            border-bottom: 2px solid green;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .header .top>* {
            vertical-align: top !important;
            width: 49%;
        }

        .header .top .informations {
            text-align: right;
        }

        .header .bottom>* {
            vertical-align: top;
        }

        .header .bottom .references {
            width: 66%;
        }

        .header .bottom .references p {
            margin-top: 0;
            font-weight: bold;
        }

        .header .bottom .date {
            width: 33%;
            text-align: right;
        }

        .code>* {
            padding: 0.5rem;
        }

        .code h2 {
            margin-bottom: 0;
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }

        .code p {
            margin-top: 0;
            border-bottom: 1px solid black;
        }

        .code p * {
            display: inline-block;
            vertical-align: top;
        }

        .code p strong {
            font-size: 25px;
            margin-left: 1rem;
        }

        table {
            border-collapse: collapse;
        }

        table td {
            padding: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="top">
            <div>
                <img src="{{ asset('logo.png') }}" alt="Logo de l'ANaTT" style="width: 300px; height: auto;">
            </div>

            <div class="informations">
                <ul>
                    <li>01 BP 1230 Cotonou</li>
                    <li>Tél +229 21 31 39 98</li>
                    <li>+229 21 31 39 98</li>
                    <li>anatt.contact@gouv.bj</li>
                    <li>Rue 108 - Tokpa Hoho Lot 35B Immeuble KOUGBLENOU</li>
                </ul>
            </div>
        </div>

        <div class="bottom">
            <div class="references">
                <p>AGENCE NATIONALE DES TRANSPORTS TERRESTRES (ANaTT)</p>
                <p>N° ______/ANaTT/MCVDD/DTIT/ADT-ATL/SPC/DPC/SA</p>
            </div>
            <div class="date">Cotonou, le {{ now()->format('d M Y') }}</div>
        </div>

        <div>EXAMEN DE PERMIS DE CONDUIRE - {{ strtoupper($examen_name) }}</div>
    </div>

    <div class="content">
        <h1 style="text-align: center; margin-top: 20px;">
            <span style="line-height: 25px">
                ATTESTATION DE SUCCES A L'EXAMEN DE PERMIS DE CONDUIRE <br />
                ET D'AUTORISATION TEMPORAIRE DE CONDUIRE
            </span>
        </h1>


        <div>
            <h2 style="margin-top: 30px;" class="title">Informations du candidat</h2>

            <table width="100%" border="1">
                <tr>
                    <td class="title">NPI</td>
                    <td>{{ $npi }}</td>
                </tr>
                <tr>
                    <td class="title">Nom</td>
                    <td>{{ $nom }}</td>
                </tr>
                <tr>
                    <td class="title">Prénoms</td>
                    <td>{{ $prenoms }}</td>
                </tr>
                <tr>
                    <td class="title">Catégorie de permis</td>
                    <td>{{ $cat_permis }}</td> <!-- Vous pouvez remplacer par la catégorie si nécessaire -->
                </tr>
                <tr>
                    <td class="title">Langue de composition</td>
                    <td>{{ $langue }}</td>
                </tr>
                <tr>
                    <td class="title">Né(e) le</td>
                    <td>{{ \Carbon\Carbon::parse($date_de_naissance)->format('d/m/Y') }} à {{ $lieu_de_naissance }}</td>
                </tr>
                <tr>
                    <td>

                        <img src="{{ $qr_code_path }}" alt="QR Code" width="100" />

                    </td>
                    <td>
                        <div style="font-size: 22px">
                            Scannez ce code QR pour vérifier l'authenticité de ce document.
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="text-align: right">
            <p>Le Directeur Général</p>
            <table style="margin-left: auto">
                <tr>
                    <td style="text-align: right"><img src="{{ asset('permis/signature.png') }}" alt="Signature" />
                    </td>
                </tr>
            </table>
            <span>&nbsp;Richard DADA</span>
        </div>

        <footer style="margin-top: 30px">
            <table class="my" width="100%">
                <tr>
                    <td style="padding: 0">
                        <div style="
                  width: 100%;
                  height: 5px;
                  background-color: #06650b;
                  border-radius: 5px 0 0 5px;
                  display: block;
                  margin: 0;
                "></div>
                    </td>
                    <td style="padding: 0">
                        <div style="
                  width: 100%;
                  height: 5px;
                  background-color: #fad708;
                  margin-left: -2px;
                  display: block;
                  margin: 0;
                "></div>
                    </td>
                    <td style="padding: 0">
                        <div style="
                  width: 100%;
                  height: 5px;
                  background-color: #d40708;
                  border-radius: 0 5px 5px 0;
                  display: block;
                  margin: 0;
                "></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        Ce document doit être accompagné de la pièce d'identité pour
                        servir d'autorisation de conduire sur le territoire national.
                        L'intéressé (e) jouit de tout droit de conduire des véhicules de
                        la catégorie ci-dessus cités.
                    </td>
                </tr>
            </table>
        </footer>
    </div>
</body>

</html>
