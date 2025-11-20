<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//FR" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title>Document</title>

    <style type="text/css">
        @import url("https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,400;1,500;1,600;1,700;1,800&display=swap");

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
            font-size: 11px;
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
            vertical-align: top;
            width: 100%;
            font-size: 11px;
        }

        .p-3 {
            padding: .7rem !important;
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
            margin: 1.5em 0;
        }

        td {
            border: 1px solid rgb(0, 184, 0) !important;
            text-align: center;
            font-weight: 500;
            padding: .7rem !important;
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
            padding: 0.5rem;
        }

        .bg-body td {
            padding: 0.5rem;
        }

        .w-50 {
            width: 50px;
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
        @foreach ($sessions as $session)
            <div class="my-5">
                <table class="table">
                    <tbody>
                        <tr class="bg-primary" style="text-transform: uppercase;">
                            <td colspan="9" class="text-white fw-bold text-center fs-4" style="padding: 1rem">
                                {{ $session['session_long'] }}
                            </td>
                        </tr>
                        <tr class="bg-body text-primary" align="middle" style="vertical-align: middle">
                            <td colspan="2">Etude des dossiers</td>
                            <td colspan="2">Gestions des rejets</td>
                            <td colspan="2">Programmation</td>
                            <td>Convocation</td>
                            <td>Composition code</td>
                            <td>Composition conduite</td>
                        </tr>
                        <tr class="bg-body">
                            <td>
                                Début
                            </td>
                            <td>
                                Fin
                            </td>

                            <td>
                                Début
                            </td>
                            <td>Fin</td>

                            <td>Début</td>
                            <td>Fin</td>

                            <td>Démarrage</td>
                            <td>Démarrage</td>
                            <td>Démarrage</td>
                        </tr>
                        <tr class="data">
                            {{--  Etude dossiers --}}
                            <td class="py-3">
                                <span class="date">
                                    {{ \App\Services\Help::date($session['debut_etude_dossier_at'], 'DD MMM') }}
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="date">
                                    {{ \App\Services\Help::date($session['fin_etude_dossier_at'], 'DD MMM') }}
                                </span>
                            </td>

                            {{-- Gestions des dossiers --}}
                            <td class="py-3">
                                <span class="date">
                                    {{ \App\Services\Help::date($session['debut_gestion_rejet_at'], 'DD MMM') }}
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="date">
                                    {{ \App\Services\Help::date($session['fin_gestion_rejet_at'], 'DD MMM') }}
                                </span>
                            </td>

                            {{-- Programmation --}}
                            <td class="py-3">
                                <span class="date">
                                    {{ \App\Services\Help::date($session['fin_gestion_rejet_at'], 'DD MMM') }}
                                </span>
                            </td>

                            <td class="py-3">
                                <span class="date">
                                    {{ \App\Services\Help::date($session['date_convocation'], 'DD MMM') }}
                                </span>
                            </td>
                            {{-- Convocation --}}
                            <td class="py-3">
                                <span class="date">
                                    {{ \App\Services\Help::date($session['date_convocation'], 'DD MMM') }}
                                </span>
                            </td>

                            {{-- Composition code --}}
                            <td class="py-3" style="background: rgba(244, 36, 67, 0.1)">
                                <span class="date">
                                    {{ \App\Services\Help::date($session['date_code'], 'DD MMM') }}
                                </span>
                            </td>

                            {{-- Composition conduite --}}
                            <td class="py-3">
                                <span class="date">
                                    {{ \App\Services\Help::date($session['date_conduite'], 'DD MMM') }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach

        <div style="text-align: right; margin-top: 50px">
            <p>ANaTT</p>
        </div>
    </div>
</body>

</html>
