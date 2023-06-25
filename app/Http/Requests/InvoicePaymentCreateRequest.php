<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoicePaymentCreateRequest extends FormRequest
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
            "amount"=>"required|numeric",
            "payment_method"=>"required|string",
            "payment_date"=>"required|date",
            "invoice_id"=>"required|numeric|exists:invoices,id",
        ];

        // return [
        //     "invoice_id"=>"required|numeric|exists:invoices,id",
        //     "invoice_payments"=>"nullable|array",
        //     "invoice_payments.*.id"=>"nullable|numeric|exists:invoice_payments,id",
        //     "invoice_payments.*.amount"=>"required|numeric",
        //     "invoice_payments.*.payment_method"=>"required|string",
        //     "invoice_payments.*.payment_date"=>"required|date",
        // ];
    }
}
