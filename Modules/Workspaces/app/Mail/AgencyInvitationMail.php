<?php

namespace Modules\Workspaces\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Workspaces\Models\AgencyInvitation;

class AgencyInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AgencyInvitation $invitation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitación a SocialPulse — '.$this->invitation->agency->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'workspaces::mail.agency-invitation',
        );
    }
}
