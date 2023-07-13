<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendInvoicePaymentReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "invoice_id" => "required|numeric|exists:invoices,id",
            "invoice_payment_id" => "required|numeric|exists:invoice_payments,id",
            "from"=>"required|string|email",
            "to"=>"required|array",
            "to.*"=>"string|email",
            "subject"=>"required|string",

            "message" => "required|string" ,
            "copy_to_myself"=>"required|boolean",

        ];
    }
}
