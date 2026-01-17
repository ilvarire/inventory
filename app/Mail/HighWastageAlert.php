<?php

namespace App\Mail;

use App\Models\Section;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HighWastageAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Section $section,
        public float $threshold,
        public float $actualWastage,
        public float $costImpact
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚨 High Wastage Alert: ' . $this->section->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.high-wastage-alert',
            with: [
                'section' => $this->section,
                'threshold' => $this->threshold,
                'actualWastage' => $this->actualWastage,
                'costImpact' => $this->costImpact,
                'exceedancePercentage' => (($this->actualWastage - $this->threshold) / max($this->threshold, 1)) * 100,
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
