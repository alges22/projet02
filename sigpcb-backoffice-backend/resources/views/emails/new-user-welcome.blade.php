<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <title>Votre code de connexion</title>
        <style>
           
            body {
                font-family: Arial, sans-serif;
                font-size: 16px;
                color: #333;
                background-color: #f5f5f5;
            }
            h1 {
                font-size: 22px;
                font-weight: bold;
                text-align: center;
                margin-top: 50px;
            }
            .message {
                max-width: 600px;
                margin: 0 auto;
                padding: 18px;
                border: 1px solid #ccc;
                background-color: #fff;
            }
            p {
                font-size: 15px;
                line-height: 1.5;
                margin: 20px 0;
            }
            .note {
                font-size: 11px;
                line-height: 1.5;
                font-style: italic;
                margin-top: 30px;
            }
        </style>
    </head>
    <body>
        <h1> <p>Bienvenue {{ $user->first_name }} {{ $user->last_name }}, </p></h1>
        <div class="message">
            <p>Cher utilisateur,</p>
            <p>Un compte vous a été créé sur la plateforme d'administration de permis. </p></br></br>

            <p>Merci de vous connecter avec ces identifiants: </p></br></br>

            <p>Email: {{ $user->email }} </p></br></br>

            <p>Vous pouvez vous connecter en suivant ce lien: <a href="https://backofficesigpcb.anatt.bj">Se connecter</a></p>

            <p>Merci d'utiliser SIGPCB !</p>
            <div class="note">
                <p><em>Note : Ce message a été généré automatiquement. Veuillez ne pas y répondre directement.</em></p>
            </div>
        </div>
    </body>
</html>


