<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceReminderNumberToDateCreateRequest extends FormRequest
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
            "send_reminder"=>"required|boolean",
            "reminder_date_amount"=>"required|numeric",
            "invoice_id"=>"required|numeric|exists:invoices,id",
        ];
    }
}
