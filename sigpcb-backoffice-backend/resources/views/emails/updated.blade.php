<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre compte a été mis à jour</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            margin-top: 0;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .note {
            margin-top: 20px;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bonjour {{ $user->first_name }} {{ $user->last_name }}</h1>
        <p>Cher utilisateur,</p>
        <p>Votre compte a été mis à jour sur la plateforme SIGPCB.</p>
        <table>
            <tr>
                <th>Attribut</th>
                <th>Valeur</th>
            </tr>
            <tr>
                <td>Email</td>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <td>Rôle</td>
                <td>{{ $user->roles->pluck('name')->implode(', ') }}</td>
            </tr>
            <tr>
                <td>Statut</td>
                <td>{{ $user->status ? 'Actif' : 'Inactif' }}</td>
            </tr>
            <tr>
                <td>Titre</td>
                <td>{{ $user->titre ? $user->titre->name : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Unité Administrative</td>
                <td>{{ $user->uniteAdmin ? $user->uniteAdmin->name : 'N/A' }}</td>
            </tr>
            <tr>
                <td>NPI</td>
                <td>{{ $user->npi }}</td>
            </tr>
            <tr>
                <td>Téléphone</td>
                <td>{{ $user->phone }}</td>
            </tr>
        </table>
        <p>Merci d'utiliser SIGPCB !</p>
        <div class="note">
            <p><em>Note : Ce message a été généré automatiquement. Veuillez ne pas y répondre directement.</em></p>
        </div>
    </div>
</body>
</html>
