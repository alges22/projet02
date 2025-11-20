<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <title>Your login code</title>
        <style>
            /* Ajoutez votre propre style pour personnaliser l'apparence de la page ici */
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
        <h1>Your login code</h1>
        <div class="message">
            <p>Dear user,</p>
            <p>Your connection code to our application is:<strong>{{ $otp }}</strong></p>
            <p>This code is <strong> single-use</strong>  and must be used to access your account. Do not share it with anyone and do not expose it to third parties.</p>
            <p>SIGPCB cares about your security and encourages you to be vigilant when using our application.</p>
            <p>Thank you for using SIGPCB !</p>
            <div class="note">
                <p><em>Note: This message was generated automatically. Please do not answer them directly.</em></p>
            </div>
        </div>
    </body>
</html>