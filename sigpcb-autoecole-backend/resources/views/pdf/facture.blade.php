<!DOCTYPE html>
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
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
            text-align: center;
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

        .header .top .logo img {
            max-width: 100%;
        }

        .header .top .informations ul {
            padding-left: 0;
            list-style: none;
            margin: 0;
        }

        .header .top .informations li {
            margin-top: 0.2rem;
        }

        .header .bottom>* {
            vertical-align: top;
        }

        .header .bottom .date {
            width: 33%;
            text-align: right;
        }

        .code {
            padding: 0.5rem;
        }

        .code h2 {
            margin: 0;
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            text-align: center;
        }

        .code p {
            margin-top: 0;
            padding: 0.2rem 0;
            text-align: center;
        }

        .code p strong {
            font-size: 25px;
            margin-left: 1rem;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table td {
            padding: 0.5rem;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .content {
            text-align: center;
        }

        .content p {
            text-align: right;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="top">
            <div class="logo">
                <img src="{{ $logo }}" alt="Logo de l'ANaTT">
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
            <div class="date" style="text-align: right;">Cotonou, le {{ $today }}
            </div>
        </div>
    </div>

    <div class="content">
        <h1><span style="text-decoration: underline;">FACTURE</span></h1>
        <div>
            <h2 class="title">Paiement effectué avec succès</h2>

            <table>
                <tr>
                    <td class="title">NPI</td>
                    <td>{{ $promoteur['npi'] }}</td>
                </tr>
                <tr>
                    <td class="title">Nom</td>
                    <td>{{ $promoteur['nom'] }}</td>
                </tr>
                <tr>
                    <td class="title">Prénoms</td>
                    <td>{{ $promoteur['prenoms'] }}</td>
                </tr>
                <tr>
                    <td class="title">Service</td>
                    <td> {{ $service }} </td>
                </tr>

                <tr>
                    <td class="title">Montant payé</td>
                    <td>{{ $transaction['montant'] }}</td>
                </tr>
                <tr>
                    <td class="title">Numéro de paiement</td>
                    <td>{{ $transaction['phone'] }}</td>
                </tr>
                <tr>
                    <td class="title">Date de paiement</td>
                    <td>{{ now()->isoFormat('dddd DD MMMM YYYY', 'Do MMMM YYYY') }}
                    </td>
                </tr>
            </table>
        </div>

        <div style="text-align: right; margin-top:150px">
            <p> Directeur de l'Administration et des Finances (DAF/ANaTT)</p>

        </div>
    </div>
</body>

</html>
