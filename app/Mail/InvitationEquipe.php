<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationEquipe extends Mailable
{
    use Queueable, SerializesModels;
    //pour rendre l'invitation accessible directement dans le fichier HTML de l'email
    public $invitation;

    /**
     * Create a new message instance.
     */
    public function __construct(Invitation $invitation){
        $this->invitation = $invitation;
    }
    

    /**
     * Get the message envelope.
     */
    //ici c'est pour configurer l'entete de l'email en mode le sujet 
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation à rejoindre l’équipe : ' . $this->invitation->equipe->nom,
        );
    }

    /**
     * Get the message content definition.
     */
    //genre c'est ici on fait la liaison avec le fichier de vue html/blade 
    public function content(): Content
    {
        return new Content(
            //vu que $invitation est public il sera directement transmis 
            view: 'emails.invitation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
