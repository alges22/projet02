<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//FR" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title>Document</title>

    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,400;1,500;1,600;1,700;1,800&display=swap');

        body {
            font-family: Montserrat, sans-serif;
            font-size: 12px;
        }


        .text-primary {
            color: #006f6f;
        }

        .bg-primary {
            background-color: #006f6f;
        }


        .text-white {
            color: #fff;
        }


        .fw-bold {
            font-weight: 900;
        }

        .text-center {
            text-align: center;
        }

        h1,
        h2 {
            font-size: 13px;
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
            margin-bottom: 1rem;
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



        table {
            border-collapse: collapse;
            box-sizing: border-box;
            font-weight: 400;
            margin-bottom: 0px;
            text-transform: uppercase;
            vertical-align: top;
            width: 100%;
            font-size: 11px;
        }


        .p-3 {
            padding: .5rem !important;
        }

        .border-primary {
            opacity: 1;
            border: 1px solid rgba(0, 111, 111);
        }

        .bg-body {
            background-color: #ddf0f0;
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .my-5 {
            margin: 1em 0;
        }

        td {
            border: 1px solid rgb(0, 184, 0) !important;
            text-align: center;
            font-weight: 500;
        }

        td.bt-0 {
            border-top: none !important;
        }

        td.bb-0 {
            border-bottom: none !important;
        }

        .vm {
            vertical-align: middle;
        }

        .vb {
            bottom: -25px;
        }

        .bg-body {

            td,
            tr {
                color: var(--bs-primary);
            }
        }

        .fs-4 {
            font-size: 1rem !important;
        }

        .stats td {
            padding: .5rem;
        }

        .bg-body td {
            padding: 0.5rem;
        }

        tr,
        td {
            width: 100%
        }

        .w-50 {
            width: 50px;
        }
    </style>
</head>

<body>
    <div>
        <div class="header" style="font-size:12px">
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
                    @if (!is_null($annexe))
                        <p>{{ $annexe['name'] }}</p>
                    @endif
                    <p>N° ______/ANaTT/MCVDD/DTIT/ADT-ATL/SPC/DPC/SA</p>
                </div>

                <div class="date">Cotonou, le {{ $today }}</div>
            </div>

            <div>EXAMEN DE PERMIS DE CONDUIRE - {{ strtoupper($session) }}</div>
        </div>

        <div class="content">
            <div class="p-3 border-primary mt-3 bg-body" style="border-left-width: 3px; font-size:12px">
                <div class="mb-2">
                    <strong class="fw-semibold">Rapport syntéthique</strong>
                    : {{ $session }}
                </div>
                <div class="mb-2">
                    <strong class="fw-semibold">Annexe </strong> :
                    @if (is_null($annexe))
                        Toutes les annexes
                    @else
                        {{ $annexe['name'] }}
                    @endif
                </div>
                <div>
                    <strong class="fw-semibold">Candidats présentés </strong>
                    : {{ $total }}
                </div>
            </div>

            <div class="my-5" style="text-transform: uppercase">
                <div style=" border-right: 1px solid rgb(0, 184, 0) !important;">
                    <div class="bg-primary text-white fw-bold text-center fs-4" style="width: 100%;padding: 1rem 0;">
                        STATISTIQUES DE SESSION
                    </div>
                    <table class="table">
                        <tbody>

                            <tr class="bg-body text-primary" align="middle" style="vertical-align: middle">
                                <td colspan="1" class="bb-0">
                                    <div class="vb">Candidats</div>
                                </td>
                                <td colspan="{{ count($langues) }}">Langues</td>
                                <td colspan="2">Sexe</td>
                                <td class="bb-0">
                                    <div class="vb">Tt</div>
                                </td>
                                <td class="bb-0">
                                    <div class="vb">
                                        (%)
                                    </div>
                                </td>
                            </tr>
                            <tr class="bg-body">
                                <td class="bt-0"></td>

                                @foreach ($langues as $lg)
                                    <td>
                                        <div class="diagonal-text">{{ substr($lg['name'], 0, 3) }}</div>
                                    </td>
                                @endforeach
                                <td>M</td>
                                <td>F</td>
                                <td class="bt-0"></td>
                                <td class="bt-0"></td>
                            </tr>
                            @foreach ($data as $item)
                                <tr class="stats">
                                    <td class="py-3">{{ $item['name'] }}</td>

                                    @foreach ($item['langues'] as $slg)
                                        <td>
                                            {{ $slg['count'] }}
                                        </td>
                                    @endforeach
                                    @foreach ($item['sexes'] as $sexe)
                                        <td>
                                            {{ $sexe['count'] }}
                                        </td>
                                    @endforeach
                                    <td class="py-3">{{ $item['total'] }}</td>
                                    <td class="py-3">{{ $item['percent'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="text-align: right; margin-top: 5px">
                <p>ANaTT</p>
            </div>
        </div>
    </div>
</body>

</html>
