<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenancyAgreementExpiryReminderMail extends Mailable
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
        $days_difference = !empty($this->agreement->tenant_contact_expired_date) 
            ? now()->diffInDays($this->agreement->tenant_contact_expired_date) 
            : 'N/A';
        return $this->subject($this->title)
            ->view('email.tenancy_agreement_expiry_reminder')
            ->with([
                'title' => $this->title,
                'message_desc' => ($this->reminder->send_time == "after_expiry")
                    ? ("The tenancy agreement for your property expired " . $days_difference . " days ago. Please renew it now.")
                    : ("The tenancy agreement for your property will expire in " . $days_difference . " days. Please renew it in time."),
                'agreement' => $this->agreement,
                'property' => $this->property,
                'business' => $this->business
            ]);
    }
}
