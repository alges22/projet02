<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//FR" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title> </title>
    <style type="text/css">
        body,
        html {

            margin: auto;
            font-family: 'Poppins', sans-serif;

        }

        .header {
            padding: 10px 0 10px 20px;
            border-radius: 16px 16px 0 0;
        }

        .header>*>* {
            display: inline-block;
        }

        .header .top>* {
            vertical-align: top !important;
            width: 49%;
        }

        .header .top .informations {
            text-align: right;
        }

        .bottom>* {
            vertical-align: top;
        }


        table,
        tbody,
        th,
        td {
            font-family: 'Poppins', sans-serif;
            border-collapse: collapse;
        }

        tbody,
        thead,
        table {
            width: 100%;
        }


        .content {
            padding: 15px;
            position: relative;
            z-index: 2;
        }

        .informations img {
            width: 250px;
        }

        .fw-bold {
            font-weight: 500;
        }


        .txt-r {
            text-align: right;
        }

        .txt-l {
            text-align: left;
        }



        small {
            font-weight: 100 !important;
            font-size: 8px;
        }

        .my {
            margin: 10px 0;
        }

        .bg-jaune {
            width: 100%;
            height: 3px;
            background-color: #fad708;
            margin-left: -2px;
        }

        .bg-green {
            width: 100%;
            height: 3px;
            background-color: #06650b;
            border-radius: 5px 0 0 5px;
        }

        .bg-red {
            width: 100%;
            height: 3px;
            background-color: #d40708;
            border-radius: 0 5px 5 0;
            margin-left: -4px;
        }

        .vt {
            vertical-align: top;
        }

        .filgrand {
            top: 25px;
            left: 0;
            text-align: center;
            position: absolute;
            right: 0;
            opacity: 0.1;
            z-index: 0;
        }

        p {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }


        .legends p {
            font-size: 8px;
        }

        .legends td {
            vertical-align: top;
        }

        .col-2 th {
            border-bottom: 1px solid #666666;
            padding-top: 5px;
            padding-bottom: 5px;
            border-right: 1px solid #666666;
        }

        .fw-bolder {
            font-weight: 600;
        }

        ul {
            list-style: none;
        }
    </style>
    <style type="text/css">
        .text-primary {
            color: #735459;
        }

        .bg-primary {
            background-color: #735459;
        }

        .bg-second {
            background-color: #ffedf0;
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

        ul {
            padding: 0;
        }

        ul li {
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
                <p>N° ______/ANaTT/MCVDD/DTIT/ADT-ATL/SPC/DPC/SA</p>
            </div>

            <div class="date">Cotonou, le {{ $today }}</div>
        </div>


    </div>

    <div class="content">

        <h3 style="text-transform: uppercase;" class="text-center"> LICENCE D’EXPLOITATION D’AUTO-ÉCOLE</h3>
        <div style="font-size: 12px">
            <p class="my"><strong>Date d'émission</strong> : {{ $date_debut }}</p>
            <p class="my"><strong>Date d'expiration</strong> : {{ $date_fin }}</p>
            <p class="my"><strong>Numéro Licence : {{ $licence->code }}</p>
        </div>

        <table>
            <tbody>
                <tr>
                    <td>
                        <h3>INFORMATION DE L’AUTO-ÉCOLE</h3>
                        <p class="my"><strong>Référence de l'autorisation </strong> : {{ $agrement->code }} </p>
                        <p class="my"><strong>Nom</strong> : {{ $ae->name }} </p>
                        <p class="my"><strong>Adresse</strong> : {{ $ae->adresse }} </p>
                        <p><strong>Nom du Directeur</strong>: {{ $promoteur['nom'] }} {{ $promoteur['prenoms'] }} </p>
                        <p><strong>Code</strong>: {{ $ae->code }} </p>
                    </td>
                    <td>
                        <h3> MONITEURS & VEHICULES</h3>
                        @foreach ($moniteurs as $key => $monitor)
                            <p class="my">
                                <strong>Moniteur {{ $key + 1 }}</strong> : {{ $monitor['nom'] }}
                                {{ $monitor['prenoms'] }}
                            </p>
                        @endforeach
                        <div style="margin-top: 10px;"></div>
                        @foreach ($vehicules as $key => $vehicule)
                            <p class="my">
                                <strong>Véhicule {{ $key + 1 }}</strong> : {{ $vehicule['immatriculation'] }}
                            </p>
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
        <div style="text-align: right; margin-top:120px">
            <div class="my">
                <div class="fw-bolder" style="display: inline-block; position:relative; top:6px;">
                    <table>
                        <tr>
                            <td><img src="{{ $signature }}"></td>
                            <td>
                                <span style="font-size: 11px">
                                    {{ \Carbon\Carbon::parse($licence->created_at)->format('Y.m.d') }}</span>
                                <br>
                                <span style="font-size: 11px">
                                    {{ \Carbon\Carbon::parse($licence->created_at)->format('H:i:s') }}</span>
                            </td>
                        </tr>
                    </table>
                    <span>&nbsp;{{ $signataire }} </span>
                </div>
            </div>
        </div>
        <table class="my">
            <tr>
                <td>
                    <div class="bg-green"></div>
                </td>
                <td>
                    <div class="bg-jaune"></div>
                </td>
                <td>
                    <div class="bg-red"></div>
                </td>
            </tr>
        </table>
        <table>
            <tbody>
                <td style="font-size: 12px">
                    <p>
                        <strong>VÉRIFIEZ LA CONFORMITÉ DE CE DOCUMENT</strong>
                    </p>
                    <ol>
                        <li class="my">

                            Suivez les instructions de http://permisdeconduire_DL.anatt.gouv.bj/
                        </li>
                        <li class="my">

                            Utilisez le numéro de l'agrément {{ $licence->code }}
                        </li>
                        <li class="my">

                            Assurez vous que le document est identique à celui en
                            ligne
                        </li>
                    </ol>
                </td>

                <td>
                    <table>
                        <tr>
                            <td>
                                <p style="text-align:center; margin-top:15px;">

                                </p>
                            </td>

                        </tr>
                    </table>
                </td>
            </tbody>
        </table>
    </div>
</body>

</html>
