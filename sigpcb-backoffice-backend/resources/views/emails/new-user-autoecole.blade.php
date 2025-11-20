<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <title>Création de compte</title>
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
        <h1>Création de compte</h1>
        <div class="message">
                <p>Cher utilisateur,</p>
                <p>Félicitations ! Votre demande d'agrément a été validée. Votre compte Auto-École <strong>{{$name}}</strong> a été créé avec succès. Nous sommes ravis de vous accueillir parmi nous.</p>
                <p>Votre code personnel (Ce code est à transmettre à vos candidats pour leur préinscription) : <strong>{{ $code }}</strong></p>
                <p>Pour accéder à votre compte, veuillez cliquer sur le lien ci-dessous :</p>
                <center>
                        <a href="{{ env('APP_LINK') }}" style="display: inline-block; padding: 12px 24px; background-color: #52a8b9; color: #ffffff; text-decoration: none;">Se Connecter</a>
                </center>
                <p>Une fois connecté, vous aurez accès à toutes les fonctionnalités de notre plateforme et pourrez commencer à gérer votre auto-école en ligne.</p>
                <p>SIGPCB est soucieux de votre sécurité et vous encourage à être vigilant lors de l'utilisation de notre application.</p>
                <p>Merci d'utiliser SIGPCB !</p>
                <div class="note">
                        <p><em>Note : Ce message a été généré automatiquement. Veuillez ne pas y répondre directement.</em></p>
                </div>
        </div>
    </body>
</html>