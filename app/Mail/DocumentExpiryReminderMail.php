<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;



class DocumentExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $message;
    public $document;
    public $property;
    public $business;

    public function __construct($title, $message, $document, $property, $business)
    {
        $this->title = $title;
        $this->message = $message;
        $this->document = $document;
        $this->property = $property;
        $this->business = $business;
    }

    public function build()
    {
        return $this->subject($this->title)
            ->view('email.document_expiry_reminder')
            ->with([
                'message' => $this->message,
                'document' => $this->document,
                'property' => $this->property,
                'business' => $this->business
            ]);
    }
}
