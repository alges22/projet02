<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//FR" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title> </title>

    <style type="text/css">
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
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
            padding: 10px;
        }

        .text-white {
            color: #fff;
        }

        .p-3 {
            padding: 10px;
        }

        .fw-bold {
            font-weight: 900;
        }

        .text-center {
            text-align: center;
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

        <div>EXAMEN DE PERMIS DE CONDUIRE - {{ strtoupper($session) }}</div>
    </div>

    <div class="content">
        <h1 style="text-align: center">
            <span style="text-decoration: underline" class="fw-bold">NOTE DE SERVICE
            </span>
        </h1>

        <div class="fw-bold text-center" style="width: 70%; margin: auto">
            Suite à l'étude des dossiers de candidature pour les examens de permis
            de conduire session: {{ $session }} à : {{ $annexe->name }}, les personnes dont
            les noms suivent sont invitées à participer à l'épreuve du code de la
            route, le {{ $date_code }}.
        </div>
        @foreach ($collection as $key => $item)
            <div class="page-break">
                <h2 class="text-primary">{{ $item['date'] }}</h2>

                @foreach ($item['programmations'] as $programmation)
                    <div class="bg-primary p-3 text-white ">
                        <b style="font-size: 20px">Catégorie {{ $programmation['permis'] }} </b>
                    </div>
                    <table width="100%" border="1">
                        <thead class="bg-second">
                            <th class="p-3">N°</th>
                            <th class="p-3">Nom et prénoms</th>
                            <th class="p-3">Numéro NPI</th>
                            <th class="p-3">Auto-école</th>
                            <th class="p-3">Langue</th>
                            <th class="p-3">Salle</th>
                            <th class="p-3">Table</th>
                            <th class="p-3">Vague</th>
                        </thead>

                        <tbody>
                            @foreach ($programmation['candidats'] as $k => $candidat)
                                <tr>
                                    <td class="p-3">{{ $k + 1 }}</td>
                                    <td class="p-3">{{ $candidat['candidat']['nom'] }}
                                        {{ $candidat['candidat']['prenoms'] }}</td>
                                    <td class="p-3">{{ $candidat['candidat']['npi'] }} </td>
                                    <td class="p-3">{{ $candidat['auto_ecole']['name'] }} </td>
                                    <td class="p-3">{{ $candidat['langue']['name'] }} </td>
                                    <td class="p-3">{{ $candidat['salle']['name'] }} </td>
                                    <td class="p-3">{{ str_pad($candidat['num_table'], 2, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="p-3">
                                        {{ str_pad($candidat['vague']['numero'], 2, '0', STR_PAD_LEFT) }} </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                @endforeach
            </div>
        @endforeach
        <div style="text-align: right">
            <p>Le Directeur Général PO,</p>
            <p>Le Chef de: {{ $annexe->name }}</p>
        </div>
    </div>
</body>

</html>
