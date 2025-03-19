<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MaintenanceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $reminder;
    public $property;
    public $business;
    public $inspection;

    public function __construct($title, $reminder, $property, $business, $inspection)
    {
        $this->title = $title;
        $this->reminder = $reminder;
        $this->property = $property;
        $this->business = $business;
        $this->inspection = $inspection;
    }

    public function build()
    {
        $days_difference = now()->diffInDays($this->inspection->next_inspection_date);
        return $this->subject($this->title)
            ->view('email.maintenance_reminder')
            ->with([
                'title' => $this->title,
                'message_desc' => ($this->reminder->send_time == "after_expiry")
                    ? ("The inspection for your property was due " . $days_difference . " days ago. Please schedule it now.")
                    : ("The next inspection for your property is in " . $days_difference . " days. Please prepare in advance."),
                'property' => $this->property,
                'business' => $this->business,
                'inspection' => $this->inspection
            ]);
    }



}
