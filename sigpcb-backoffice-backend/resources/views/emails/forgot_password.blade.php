<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <title>Réinitialisation de mot de passe</title>
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
        <h1>Réinitialisation de mot de passe</h1>
        <div class="message">
            <p>Cher utilisateur,</p>
            <p>Cliquez sur le bouton ci-dessous pour réinitialiser votre mot de passe :</p>
            <center>
                <a href="{{ $envoie }}" style="display: inline-block; padding: 12px 24px; background-color: #52a8b9; color: #ffffff; text-decoration: none;">Réinitialiser mon mot de passe</a>
            </center>
            <p>Ce lien <strong>expire dans 15min</strong> et doit être utilisé pour accéder à votre compte. Ne le partagez avec personne et ne l'exposez pas à des tiers.</p>
            <p>Si vous n'avez pas demandé de réinitialisation de mot de passe, ignorez simplement cet e-mail.</p>
            <p>SIGPCB est soucieux de votre sécurité et vous encourage à être vigilant lors de l'utilisation de notre application.</p>
            <p>Merci d'utiliser SIGPCB !</p>
            <div class="note">
                <p><em>Note : Ce message a été généré automatiquement. Veuillez ne pas y répondre directement.</em></p>
            </div>
        </div>
    </body>
</html>
