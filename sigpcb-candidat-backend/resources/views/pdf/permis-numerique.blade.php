<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//FR" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style type="text/css">
        body,
        html {
            padding: 15px;
            color: #3E3E3E;
            margin: auto;
            font-family: 'Poppins', sans-serif;
            font-size: 80%;
            line-height: 80%;
        }

        .header {
            background-color: #f2f2f2;
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

        .i {
            font-size: 10px;
            color: black;
            font-weight: 600;
        }

        .text-primary {
            color: #006f6f;
        }

        .container {
            width: 850px;
            margin: auto;
            background-color: #fff;
            border-radius: 16px;
            margin-top: 70px;
            border: 1 solid #000000;
        }

        .content {
            padding: 15px;
            position: relative;
            z-index: 2;
        }

        .avatar {
            width: 60px;
        }

        .informations img {
            width: 250px;
        }

        .fw-bold {
            font-weight: 500;
        }

        .permis td {
            padding: 1px 8px 2px 5px;
            vertical-align: middle;
        }

        .permis-img {
            width: 35px;
            display: inline-block;
            margin-bottom: 3px;
            right: -15px;
            position: relative;
            bottom: -3px;
        }

        .permis-name {
            font-weight: 600;
            font-size: 10px;
            display: inline-block;
            position: relative;
            left: -5px;

        }

        .txt-r {
            text-align: right;
        }

        .txt-l {
            text-align: left;
        }

        .avatar {
            width: 120px;
            border-radius: 3px;
        }

        .avatar img {
            width: 100%;
            height: auto;
            border-radius: 3px;
            border: 1px solid #DEDEDE;
        }

        small {
            font-weight: 100 !important;
            font-size: 8px;
        }

        .my {
            margin: 6px 0;
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

        .usr-infos .i {
            width: 40px;
            display: inline-block;
        }

        .usr-infos .ut {
            display: inline-block;
            position: relative;
            bottom: -1px;
            font-size: 10px;
            line-height: 98%;
            vertical-align: baseline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="top">
                <div class="logo">
                    <div class="bottom">
                        <div class="references">
                            <p style="margin-top: 15px;font-size:20px;font-weight:600">
                                Permis numérique
                            </p>
                            <p style="margin-top:12px">
                                <span class="i">4d.</span>
                                <span class="text-primary fw-bolder" style="font-size: 16px"> {{ $candidat['npi'] }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="informations">
                    <img src="{{ $logo }}" />
                </div>
            </div>
        </div>

        <div class="content">
            <div class="filgrand"><img src="{{ $amoirie }}" width="405" /></div>
            <table>
                <tbody>
                    <!--Column 1 des photos-->
                    <td>
                        <table>
                            <tbody>
                                <tr class="fw-bold">
                                    <td style="width: 30%;">
                                        <span class="i">6.</span>
                                        <div class="avatar">
                                            <img src="{{ $avatar }}" />
                                        </div>
                                        <p style="margin-top: 10px;">
                                        <table>
                                            <tr>
                                                <td>
                                                    <p><span class="i">7.</span></p>
                                                </td>
                                                <td>
                                                    <img src="{{ $candidat['signature'] ?? '' }}" width="60" alt="">
                                                </td>
                                            </tr>
                                        </table>
                                        </p>
                                    </td>
                                    <td style="width: 70%;" class="usr-infos">
                                        <div class="my">
                                            <span class="i">1.</span>
                                            <span class="fw-bolder ut">{{ $candidat['nom'] }}</span>
                                        </div>
                                        <div class="my">
                                            <span class="i">2.</span>
                                            <span class="fw-bolder ut">{{ $candidat['prenoms'] }}</span>
                                        </div>
                                        <div class="my">
                                            <span class="i">3.</span>
                                            <span class="fw-bolder ut">{{ $candidat['date_de_naissance'] }}
                                                {{ strtoupper($candidat['lieu_de_naissance']) }}
                                            </span>
                                        </div>
                                        <div class="my">
                                            <span class="i">4.a</span>
                                            <span class="fw-bolder ut">{{ $delivered_at }}</span>
                                        </div>
                                        <div class="my">
                                            <span class="i">4.b</span>
                                            <span class="fw-bolder ut">{{ $expired_at }}</span>
                                        </div>
                                        <div class="my">
                                            <span class="i" style="float: left;">4.c </span>
                                            <div class="fw-bolder"
                                                style="display: inline-block; position:relative; top:6px;">
                                                <table>
                                                    <tr>
                                                        <td><img src="{{ $signature }}"></td>
                                                        <td>
                                                            {{ \Carbon\Carbon::parse($signed_at)->format('Y.m.d') }}
                                                            <br>
                                                            {{ \Carbon\Carbon::parse($signed_at)->format('H:i:s') }}
                                                        </td>
                                                    </tr>
                                                </table>
                                                <span>&nbsp;{{ $signataire }} </span>
                                            </div>
                                        </div>
                                        <div class="my" style="margin-top: 12px;">
                                            <span class="i">5.</span>
                                            <span class="fw-bolder ut">{{ $code_permis }}</span>
                                        </div>

                                        <div class="my">
                                            <span class="i">8.</span>
                                            <span class="fw-bolder ut">{{ $candidat['adresse'] }}</span>
                                        </div>
                                        <div class="my" style="margin-bottom: 0">
                                            <span class="i">9.</span>
                                            <span class="fw-bolder ut">{{ $permis_list }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="my" style="margin-top: 0">Sexe : {{ $candidat['sexe'] }}. Groupe sanguin :
                            {{ $dossier['group_sanguin'] }}. Tél :
                            {{ $candidat['telephone'] }}.</div>
                        <div style=" border: 1px solid #006f6f; border-radius: 16px; padding: 10px;">
                            <table class="legends">
                                <tbody>
                                    <tr>
                                        <td style="width: 160px">
                                            <p>1. Nom</p>
                                            <p>2. Prénom(s)</p>
                                            <p>3. Date et lieu de naissance</p>
                                            <p>4a. Date de délivrance de la pièce</p>
                                            <p>4b. Date d’expiration de la pièce</p>
                                            <p>4c. Autorité de délivrance de la pièce</p>
                                            <p>5. Numéro du permis</p>
                                        </td>
                                        <td style="width: 180px">
                                            <p>6. Photo du titulaire</p>
                                            <p>7. Signature du titulaire</p>
                                            <p>8. Adresse</p>
                                            <p>9. Catégorie de permis</p>
                                            <p>10. Date de validité par catégorie de
                                                permis</p>
                                            <p>11. Date d’expiration par catégorie de
                                                permis</p>
                                        </td>
                                        <td>
                                            <p>12. Restrictions</p>
                                            <p>13. Informations administratives en cas de changement
                                                de pays de résidence normale</p>
                                            <p>14. Informations administratives relatives à la
                                                sécurité de la circulation routière</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                    <!--Column 2 des permis-->
                    <td style="padding-left: 15px" class="vt col-2">
                        <div style="border:1px solid #666666; border-radius: 8px;">
                            <table width="100%" border="0">
                                <thead border="0">
                                    <th>
                                        <span class="i">9.</span>
                                    </th>
                                    <th style="border-bottom:1px solid #666666;">
                                        <span class="i">10.</span>
                                    </th>
                                    <th style="border-bottom:1px solid #666666;">
                                        <span class="i">11.</span>
                                    </th>
                                    <th style=" border-right:none;">
                                        <span class="i">12.</span>
                                    </th>
                                </thead>
                                <tbody class="permis" border="0">
                                    @foreach ($permis as $pm)
                                    <tr style="border-top:1px solid #666666;">
                                        <td align="baseline" style="border-top:1px solid #666666;">
                                            <table border="0">
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <span class="permis-name">{{ $pm['name'] }}</span>
                                                        </td>
                                                        <td>
                                                            <img class="permis-img" src="{{ $pm['icon'] }}" />
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                        </td>
                                        <td style="border:1px solid #666666; font-weight:600; border-bottom:none;">
                                            {{ $pm['delivered_at'] }}</td>
                                        <td style="border:1px solid #666666; font-weight:600; border-bottom:none;">
                                            {{ $pm['expired_at'] }}</td>
                                        <td style="font-weight:600;">
                                            @foreach ($pm['restrictions'] as $rest)
                                            {{ $rest['name'] ?? '' }},
                                            @endforeach
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </td>
                </tbody>
            </table>

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
                <tbody style="font-size: 10px">
                    <td style="width: 50%">
                        <p>
                            <strong>VÉRIFIEZ LA CONFORMITÉ DE CE DOCUMENT</strong>
                        </p>
                        <p class="my">
                            <span>1.</span>
                            Suivez les instructions de http://sigpcb.anatt.bj/
                        </p>
                        <p class="my">
                            <span>2.</span>
                            Utilisez le numéro de permis {{ $code_permis }} ou scannez le
                            code QR
                        </p>
                        <p class="my">
                            <span>3.</span>
                            Assurez vous que le document est identique à celui en
                            ligne
                        </p>
                    </td>

                    <td>
                        <table>
                            <tr>
                                <td>
                                    <p style="text-align:center; margin-top:15px;">
                                        <span class="i">14.</span>
                                        <img src="{{ $codeqr }}" />
                                    </p>
                                </td>
                                <td style="text-align: right">
                                    <img src="{{ $codebar }}" style="width: 100%" />
                                </td>
                            </tr>
                        </table>
                    </td>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>