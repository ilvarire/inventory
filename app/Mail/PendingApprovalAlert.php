<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendingApprovalAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $type,
        public int $id,
        public User $requester,
        public array $details = []
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📋 Pending Approval: ' . ucfirst(str_replace('_', ' ', $this->type)) . ' #' . $this->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Generate approval URL (you can customize this based on your frontend)
        $approvalUrl = config('app.url') . '/approvals/' . $this->type . '/' . $this->id;

        return new Content(
            view: 'emails.pending-approval',
            with: [
                'type' => $this->type,
                'id' => $this->id,
                'requester' => $this->requester,
                'details' => $this->details,
                'approvalUrl' => $approvalUrl,
                'typeLabel' => ucfirst(str_replace('_', ' ', $this->type)),
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
