<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DocumentExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $reminder;
    public $document;
    public $property;
    public $business;

    public function __construct($title,$reminder,  $document, $property, $business)
    {
        $this->title = $title;
        $this->reminder = $reminder;
        $this->document = $document;
        $this->property = $property;
        $this->business = $business;
    }

    public function build()
    {
        $days_difference = now()->diffInDays($this->document->gas_end_date);
        return $this->subject($this->title)
            ->view('email.document_expiry_reminder')
            ->with([
                'title' => $this->title,


                'message_desc' =>  (($this->reminder->send_time == "after_expiry")
                ? ("The document for your property expired" .$days_difference. "days ago. Please renew it now.")
                :
                ("The document for your property will expire in" . $days_difference . "days. Please renew it in time.")),

                'document' => $this->document,
                'property' => $this->property,
                'business' => $this->business
            ]);
    }
}
