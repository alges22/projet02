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
        .container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100vh; 
        background-color: #e5f0f0;
    }

    .title {
        text-align: center;
        padding: 10px 20px; 
        margin: 5px; 
        font-size: 18px;
        color: #005e5e;
    }

    .titles {
            color: #515151;
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
            <p>{{$dossierSession['annexe']['name']}}</p>
        </div>
        <div class="date">Le {{ \Carbon\Carbon::now()->locale('fr_FR')->isoFormat('Do MMMM YYYY') }}</div>
    </div>

</div>

<div class="container">
    <div class="title">
       <strong>
        EXAMEN DU PERMIS DE CONDUIRE 
       </strong> 
    </div>
    <div class="title">
        <strong>
            EPREUVE :
        </strong>    
            CONDUITE
    </div>
</div>

<div class="content">
    <h1 style="text-align: center;"><span style="text-decoration: underline;">CONVOCATION</span></h1>

    <div>
        <span>Madame, Monsieur,</span>

        <div style="margin-top: 1rem;">
            <span>Suite à votre réussite à l’épreuve du code de l'examen du permis de conduire session du {{ \Carbon\Carbon::parse($dossierSession['examen']['date_code'])->isoFormat('dddd DD MMMM YYYY', 'Do MMMM YYYY') }}, vous êtes expressément invité à vous présenter a l'ANaTT - {{$dossierSession['annexe']['name']}} pour la composition de l’épreuve du code de la route, <span style="color:#005e5e">le {{ \Carbon\Carbon::parse($dossierSession['conduiteVague']['date_compo'])->isoFormat('dddd DD MMMM YYYY', 'Do MMMM YYYY') }} à 7h30mn, muni obligatoirement de votre pièce d'identité (carte nationale biométrique ou CIP).</span> </span>
        </div>
    </div>

    <!-- <div class="code">

        <p>
            <img src="data:image/png;base64,{{ base64_encode($qrCode) }}" alt="QR Code">
        </p>
    </div> -->

    <div>
        <h2 class="titles">Informations</h2>

        <table width="100%" border="1">
            <tr>
                <td class="titles">Nom et prénoms</td>
                <td>{{$dossierSession['candidat']['nom']}} {{$dossierSession['candidat']['prenoms']}}</td>
            </tr>
            <tr>
                <td class="titles">Numéro NPI</td>
                <td>{{$dossierSession['candidat']['npi']}}</td>
            </tr>
            <tr>
                <td class="titles">Auto-école</td>
                <td>{{$dossierSession['auto_ecole']['name']}}</td>
            </tr>
            <tr>
                <td class="titles">Langue de composition</td>
                <td>{{$dossierSession['langue']['name']}}</td>
            </tr>
            <tr>
                <td class="titles">Catégorie de permis</td>
                <td>{{$dossierSession['categorie_permis']['name']}}</td>
            </tr>
            <tr>
                <td class="titles">Vague</td>
                <td>{{$dossierSession['conduiteVague']['numero']}}</td>
            </tr>
          
        </table>
    </div>

    <div style="text-align: right">
        <p>Le Directeur Général PO,</p>
        <p>Le Chef d’{{$dossierSession['annexe']['name']}}</p>
    </div>
</div>
</body>
</html>
