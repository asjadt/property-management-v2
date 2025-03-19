<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OverallMaintainanceAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $maintainance_report;

    public function __construct($maintainance_report)
    {
        $this->maintainance_report = $maintainance_report;
    }

    public function build()
    {
        return $this->subject('Maintenance Expiry Alerts')
                    ->view('emails.maintainance_alert')
                    ->with([
                        'maintainance_report' => $this->maintainance_report,
                    ]);
    }
    
}
