<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Applicant;
use Illuminate\Database\Eloquent\Collection;

class ApplicantMatchingPropertiesMail extends Mailable
{
    use Queueable, SerializesModels;

    public $applicant;
    public $properties;

    /**
     * Create a new message instance.
     */
    public function __construct(Applicant $applicant, Collection $properties)
    {
        $this->applicant = $applicant;
        $this->properties = $properties;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We found properties matching your requirements!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.matching-properties',
            with: [
                'applicant' => $this->applicant,
                'properties' => $this->properties,
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
