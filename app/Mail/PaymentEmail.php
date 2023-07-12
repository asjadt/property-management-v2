<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $invoice;
    public $invoice_payment;

    public function __construct($invoice,$invoice_payment)
    {
        $this->invoice = $invoice;
        $this->invoice_payment = $invoice_payment;

    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.payment',["invoice" => $this->invoice,"invoice_payments"=>$this->invoice_payment]);
    }
}
