<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VarifyMailer extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $name;
    public $role;

    public function __construct($email, $name, $role)
    {
        $this->email = $email;
        $this->name = $name;
        $this->role = $role;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Gulf Coast Music — Role Verification Mail',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.roleVarifymail',
            with: [
                'email' => $this->email,
                'name'  => $this->name,
                'role'  => $this->role,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
