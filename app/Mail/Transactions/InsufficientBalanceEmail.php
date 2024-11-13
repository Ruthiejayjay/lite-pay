<?php

namespace App\Mail\Transactions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InsufficientBalanceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $senderAccount;
    public $attemptedAmount;

    /**
     * Create a new message instance.
     */
    public function __construct($senderAccount, $attemptedAmount)
    {
        $this->senderAccount = $senderAccount;
        $this->attemptedAmount = $attemptedAmount;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Insufficient Balance',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.transactions.insufficient-balance',
            with: [
                'senderAccount' => $this->senderAccount,
                'attemptedAmount' => $this->attemptedAmount
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
}
