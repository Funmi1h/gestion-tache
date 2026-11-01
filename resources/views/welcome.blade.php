<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Invitation à rejoindre une équipe</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px; }
        .button { display: inline-block; padding: 10px 20px; margin: 20px 0; color: #fff !important; background-color: #4F46E5; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { font-size: 12px; color: #777; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bonjour,</h2>
        
        <p>
            <strong>{{ $invitation->inviteur->nom }} {{ $invitation->inviteur->prenom }}</strong> 
            vous invite à rejoindre l'équipe <strong>{{ $invitation->equipe->nom }}</strong> sur notre plateforme.
        </p>

        @if($invitation->equipe->description)
            <p><em>Description de l'équipe : {{ $invitation->equipe->description }}</em></p>
        @endif

        <p>Pour accepter cette invitation et intégrer l'équipe, cliquez simplement sur le bouton ci-dessous :</p>
        
        <!-- Le lien contient le token unique pour identifier la transaction à l'arrivée -->
        <p style="text-align: center;">
            <a href="http://localhost:3000/invitations/valider?token={{ $invitation->token }}" class="button">
                Rejoindre l'équipe
            </a>
        </p>

        <p class="footer">
            Attention : Ce lien de validation est strictement personnel et expirera automatiquement le 
            <strong>{{ $invitation->date_expiration->format('d/m/Y à H:i') }}</strong>.<br>
            Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail.
        </p>
    </div>
</body>
</html>
