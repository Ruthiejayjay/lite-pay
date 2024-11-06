<?php

namespace App\Mail;

use App\Http\Controllers\Api\Auth\EmailVerificationController;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    protected $verificationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $verificationUrl)
    {
        $this->user = $user;
        $this->verificationUrl =  $verificationUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Our Platform',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::info('Email content set for user: ' . $this->user->email);

        return new Content(
            markdown: 'emails.welcome',
            with: [
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
            ],
        );
    }


    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        Log::info('Sending WelcomeEmail to user: ' . $this->user->email);

        return $this->markdown('emails.welcome')
            ->with([
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
            ]);
    }
}
