<?php

namespace App\Mail;

use App\Models\ProcurementItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpiryAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public ProcurementItem $batch
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $daysUntilExpiry = now()->diffInDays($this->batch->expiry_date);

        return new Envelope(
            subject: '⏰ Expiry Alert: ' . $this->batch->rawMaterial->name . ' (Expires in ' . $daysUntilExpiry . ' days)',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $daysUntilExpiry = now()->diffInDays($this->batch->expiry_date);
        $urgency = $daysUntilExpiry <= 2 ? 'critical' : ($daysUntilExpiry <= 5 ? 'high' : 'medium');

        return new Content(
            view: 'emails.expiry-alert',
            with: [
                'batch' => $this->batch,
                'daysUntilExpiry' => $daysUntilExpiry,
                'urgency' => $urgency,
                'remainingQuantity' => $this->batch->quantity - $this->batch->received_quantity,
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
