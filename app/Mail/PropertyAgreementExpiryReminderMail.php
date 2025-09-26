<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PropertyAgreementExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $reminder;
    public $agreement;
    public $property;
    public $business;

    public function __construct($title, $reminder, $agreement, $property, $business)
    {
        $this->title = $title;
        $this->reminder = $reminder;
        $this->agreement = $agreement;
        $this->property = $property;
        $this->business = $business;
    }

    public function build()
    {
        $days_difference = now()->diffInDays($this->agreement->end_date);

        return $this->subject($this->title)
            ->view('email.property_agreement_expiry_reminder')
            ->with([
                'title' => $this->title,
                'message_desc' => ($this->reminder->send_time == "after_expiry")
                    ? ("The property agreement for your property expired " . $days_difference . " days ago. Please renew it now.")
                    : ("The property agreement for your property will expire in " . $days_difference . " days. Please renew it in time."),
                'agreement' => $this->agreement,
                'property' => $this->property,
                'business' => $this->business
            ]);
    }
}
