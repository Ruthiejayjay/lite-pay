<?php

namespace App\Mail\Transactions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionSuccessfulSenderEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $senderAccount;
    public $amount;

    /**
     * Create a new message instance.
     */
    public function __construct($senderAccount, $amount)
    {
        $this->senderAccount = $senderAccount;
        $this->amount = $amount;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Transaction Successful',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.transactions.successful-sender',
            with: [
                'senderAccount' => $this->senderAccount,
                'amount' => $this->amount,
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
