<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalStatusChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $type,
        public int $id,
        public string $status,
        public User $approver,
        public ?string $notes = null
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $emoji = $this->status === 'approved' ? '✅' : '❌';
        $statusLabel = ucfirst($this->status);

        return new Envelope(
            subject: $emoji . ' ' . ucfirst(str_replace('_', ' ', $this->type)) . ' #' . $this->id . ' ' . $statusLabel,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.approval-status',
            with: [
                'type' => $this->type,
                'id' => $this->id,
                'status' => $this->status,
                'approver' => $this->approver,
                'notes' => $this->notes,
                'typeLabel' => ucfirst(str_replace('_', ' ', $this->type)),
                'isApproved' => $this->status === 'approved',
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
