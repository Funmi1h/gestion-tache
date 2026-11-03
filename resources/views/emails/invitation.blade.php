<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Invitation à rejoindre une équipe</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 30px 15px;
            color: #0f172a;
        }
        .email-wrapper {
            max-width: 540px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .email-header {
            background-color: #2563eb;
            padding: 24px;
            text-align: center;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        .email-body {
            padding: 32px 28px;
            line-height: 1.6;
            font-size: 15px;
        }
        .btn-join {
            display: block;
            width: fit-content;
            margin: 28px auto;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
        }
        .info-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            color: #475569;
            margin-top: 20px;
        }
        .email-footer {
            border-top: 1px solid #e2e8f0;
            padding: 16px;
            font-size: 12px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <h1>Taskflow</h1>
        </div>

        <div class="email-body">
            <p>Bonjour,</p>
            
            <p>
                <strong>{{ $invitation->inviteur->first_name ?? $invitation->inviteur->name ?? 'Un administrateur' }}</strong> 
                vous invite à rejoindre l'équipe <strong>{{ $invitation->equipe->nom }}</strong> sur Taskflow.
            </p>

            @if(!empty($invitation->equipe->description))
                <p style="font-style: italic; color: #475569; border-left: 3px solid #2563eb; padding-left: 12px; margin: 16px 0;">
                    « {{ $invitation->equipe->description }} »
                </p>
            @endif

            <p>
                Pour accepter cette invitation et collaborer sur les tâches de l'équipe, cliquez sur le bouton ci-dessous :
            </p>

            {{-- Construction du lien vers la page d'acceptation avec le token (CDC §5.2) --}}
            <a href="{{ url('/invitations/accepter/' . $invitation->token) }}" class="btn-join">
                Rejoindre l'équipe
            </a>

            <div class="info-box">
                ℹ️ Cette invitation est valable jusqu'au 
                <strong>{{ \Carbon\Carbon::parse($invitation->date_expiration)->translatedFormat('d F Y à H:i') }}</strong>. 
                Passé ce délai, elle sera automatiquement expirée.
            </div>
        </div>

        <div class="email-footer">
            &copy; {{ date('Y') }} Taskflow · Vous recevez ce message car une invitation a été créée pour votre adresse {{ $invitation->email }}.
        </div>
    </div>
</body>
</html>