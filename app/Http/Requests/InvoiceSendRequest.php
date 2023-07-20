<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceSendRequest extends FormRequest
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
            "id" => "required|numeric|exists:invoices,id",
            "from"=>"required|string|email",
            "to"=>"required|array",
            "to.*"=>"string|email",
            "subject"=>"required|string",

            "message" => "nullable|string" ,
            "copy_to_myself"=>"required|boolean",
            "attach_pdf"=>"required|boolean",
        ];
    }
}
