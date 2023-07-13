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
    public $request_object;
    public function __construct($invoice,$invoice_payment,$request_object)
    {
        $this->invoice = $invoice;
        $this->invoice_payment = $invoice_payment;
        $this->request_object = $request_object;

    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.payment',["invoice" => $this->invoice,"invoice_payments"=>$this->invoice_payment,"request_object"=>$this->request_object])->subject($this->request_object["subject"]);
    }
}
