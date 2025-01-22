<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $document_report;

    public function __construct($document_report)
    {
        $this->document_report = $document_report;
    }

    public function build()
    {
        return $this->subject('Document Expiry Alerts')
                    ->view('emails.document_alert')
                    ->with([
                        'document_report' => $this->document_report,
                    ]);
    }
}
