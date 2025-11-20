<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//FR" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,200;0,300;0,400;0,500;0,600;1,200;1,300;1,400;1,500&display=swap"
        rel="stylesheet">
    <title></title>
    <style type="text/css">
        body {
            font-family: "Poppins", sans-serif;
            font-weight: 400;
            font-style: normal;
            font-size: 12px;
        }

        .text-primary {
            color: #006f6f;
        }

        .bg-primary {
            background-color: #006f6f;
        }

        .bg-second {
            background-color: #ddf0f0;
        }

        .bg-second {
            padding: 7px;
        }

        .text-white {
            color: #fff;
        }

        .p-3 {
            padding: 7px;
        }

        .fw-bold {
            font-weight: 600;
        }

        .text-center {
            text-align: center;
        }

        h1,
        h2 {
            font-size: 12px;
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

        td {
            font-size: 12px;
        }

        thead,
        tbody,
        table {
            border-collapse: collapse;
            width: 100%
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="top">
            <div class="logo">
                <img src="{{ $logo }}" width="100%" />
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
                <p>{{ strtoupper($annexe->name) }}</p>
                <p>N° ______/ANaTT/MCVDD/DTIT/ADT-ATL/SPC/DPC/SA</p>
            </div>

            <div class="date">Cotonou, le {{ $today }}</div>
        </div>

        <div>EXAMEN DE PERMIS DE CONDUIRE - {{ strtoupper($session->session_long) }}</div>
    </div>

    <div class="content">
        <h1 style="text-align: center">
            <span style="text-decoration: underline" class="fw-bold">NOTE DE SERVICE
            </span>
        </h1>

        <div class="fw-bold text-center" style="width: 70%; margin: auto">
            Suite aux résultats de la session :
            {{ $session->session_long }}, les personnes dont les noms suivent sont
            priées de trouver ci-dessous leurs résultats d'examen.
        </div>
        <h2 class="text-primary">{{ $session->session_long }}</h2>

        <div class="bg-primary p-3 text-white ">
            <b style="font-size: 20px">{{ $title }} </b>
        </div>
        <table width="100%" border="1">
            <thead class="bg-second">
                <th class="p-3">N°</th>
                <th style="width: 37px;">Photos</th>
                <th class="p-3">Nom et prénoms</th>
                <th class="p-3">Numéro NPI</th>
                <th class="p-3">Permis</th>
                <th class="p-3">Auto-école</th>
                <th class="p-3">Status</th>
            </thead>
            <tbody>
                @foreach ($resultats as $k => $candidat)
                    <tr>
                        <td class="p-3">{{ $k + 1 }}</td>
                        <td style="text-align: center" align="middle">
                            <div style="text-align:center">
                                <img src="{{ data_get($candidat, 'candidat.avatar') }}" width="35" width="35" />
                            </div>
                        </td>
                        <td class="p-3">
                            {{ data_get($candidat, 'candidat.nom') }}
                            {{ data_get($candidat, 'candidat.prenoms') }}
                        </td>
                        <td class="p-3">{{ data_get($candidat, 'candidat.npi') }} </td>
                        <td class="p-3">{{ data_get($candidat, 'categoriePermis.name') }} </td>
                        <td class="p-3">{{ data_get($candidat, 'autoEcole.name') }} </td>
                        <td class="p-3">
                            @if ($result == 'admis' || $result == 'admis-code')
                                {{ data_get($candidat, 'candidat.sexe') == 'M' ? 'Admis' : 'Admise' }}
                            @elseif ($result == 'recales' || $result == 'recales-code' || $result == 'recales-conduite')
                                {{ data_get($candidat, 'candidat.sexe') == 'M' ? 'Recalé' : 'Recalée' }}
                            @elseif ($result == 'absents-code' || $result == 'absents-conduite')
                                Absent(e)
                            @elseif (data_get($candidat, 'resultat_conduite'))
                                {{ data_get($candidat, 'resultat_conduite') === 'success' ? 'Admis(e)' : 'Recalé(e)' }}
                            @else
                                --
                            @endif

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="text-align: right">
            <p>Le Directeur Général PO,</p>
            <p>Le Chef de: {{ $annexe->name }}</p>
        </div>
    </div>
</body>

</html>
