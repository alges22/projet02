<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//FR" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title> </title>

    <style type="text/css">
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 150%
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
                <p>N° ______/ANaTT/MCVDD/DTIT/ADT-ATL/SPC/DPC/SA</p>
            </div>

            <div class="date">Cotonou, le {{ $today }}</div>
        </div>


    </div>

    <div class="content" style=" font-size:14px">

        <div style="text-transform: uppercase;">
            <h3 class="text-center">Autorisation pour ouverture d'etatblissement d'enseignement de la conduite
                automobile</h3>

            <h4 class="text-center">Le directeur general de l'agence nationale des transports terrestres</h4>

            Vu le decret n° 2021-374 du 14 juillet 2021 portant approbation des status de l'agence nationale des
            transports terrestres,

            <p class="text-center"><strong>autorise</strong></p>

            <p> {{ $promoteur['sexe'] == 'M' ? 'Monsieur' : 'Madame' }}
                <strong>{{ $promoteur['nom'] }}
                    {{ $promoteur['prenoms'] }}</strong>
                domicile a {{ $promoteur['adresse'] }} ,
                tel: {{ $promoteur['telephone'] }} a ouvrir un etablissement d'enseignement de la
                conduite automobile (auto-ecole)
                Denomme <strong>
                    &#x2039;&#x2039; {{ $ae->name }} &#x203A;&#x203A;
                </strong> sis a departement: {{ $ae->departement->name }} -
                commune : {{ $ae->commune->name }}
                {{ !is_null($ae->quartier) ? '- Quartier : ' . $ae->quarter : '' }}
                {{ !is_null($ae->ilot) ? '- ILOT : ' . $ae->ilot : '' }}.
            </p>

            <p> L'interresse exercera cette activite dans le strict respect de la réglementation en vigeur en republique
                du bénin.</p>
            <p>La présente autorisation prend effet pour compter de la date de sa signature.
            </p>


        </div>

        <!------------- Footer -------------------->
        <div style="text-align: right; margin-top:60px; margin-bottom:10px">
            <div class="my">
                <p>Le Directeur général</p>
                <div class="fw-bolder" style="display: inline-block; position:relative; top:6px;">
                    <table>
                        <tr>
                            <td><img src="{{ $signature }}"></td>
                            <td>
                                <span style="font-size: 11px">
                                    {{ \Carbon\Carbon::parse($agrement->created_at)->format('Y.m.d') }}</span>
                                <br>
                                <span style="font-size: 11px">
                                    {{ \Carbon\Carbon::parse($agrement->created_at)->format('H:i:s') }}</span>
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

                            Utilisez le numéro de l'agrément {{ $agrement->code }}
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
