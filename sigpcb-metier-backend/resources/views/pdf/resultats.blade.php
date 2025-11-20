<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//FR" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>SIGPCB</title>

    <style type="text/css">
        /* @import url('https://fonts.googleapis.com/css2?family=Montserrat&display=swap'); */

        body {
            padding: 1rem 2rem;
            font-family: Montserrat, sans-serif;
            font-size: 12px;
        }

        h1, h2 {
            font-size: 14px;
        }

        ul {
            padding: 0;
        }

        ul li {
            list-style-type: none;
            margin-top: .5rem;
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

        .header > * > * {
            display: inline-block;
        }

        .header .top {
            border-bottom: 2px solid green;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .header .top > * {
            vertical-align: top !important;
            width: 49%;
        }

        .header .top .informations {
            text-align: right;
        }

        .header .bottom > * {
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

        .code > * {
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
        <div class="logo">
            <img src="{{ asset('logo.png') }}" alt="Logo de l'ANaTT" width="100%">
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
            <p>{{$annexeName}}</p>
            <p>N° ______/ANaTT/MCVDD/DTIT/ADT-ATL/SPC/DPC/SA</p>
        </div>
        <div class="date">Cotonou, le {{ \Carbon\Carbon::now()->locale('fr_FR')->isoFormat('Do MMMM YYYY') }}</div>
    </div>

    <div>EXAMEN DE RECRUTEMENT - SESSION DE {{$date_compo}}</div>
</div>

<div class="content">
    <h1 style="text-align: center;"><span style="text-decoration: underline;">Résultats</span></h1>

    <table width="100%" border="1">
        <tr>
            <th class="title">NPI</th>
            <th class="title">Nom</th>
            <th class="title">Prénoms</th>
            <th class="title">Catégorie de permis</th>
            <th class="title">Langue de composition</th>
            <th class="title">Résultat code</th>
            <th class="title">Résultat conduite</th>
            <th class="title">Résultat final</th>
        </tr>
        @foreach($dossierSession as $candidat)
            <tr>
                <td>{{$candidat->candidat_info['npi']}}</td>
                <td>{{$candidat->candidat_info['nom']}}</td>
                <td>{{$candidat->candidat_info['prenoms']}}</td>
                <td>{{$permisName}}</td>
                <td>{{$candidat->langue['name']}}</td>
                <td>{{$candidat->code_note}}</td>
                <td>{{$candidat->conduite_note}}</td>
                <td>{{$candidat->note_final}}</td>
            </tr>
        @endforeach
    </table>

    <div style="text-align: right">
        <p>Le Directeur Général PO,</p>
    </div>
</div>

</body>
</html>
