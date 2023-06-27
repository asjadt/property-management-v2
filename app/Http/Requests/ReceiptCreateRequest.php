<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiptCreateRequest extends FormRequest
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
            'tenant_id' => "required|numeric|exists:tenants,id",
            'property_address' => "required|string",
            'amount' => "required|numeric",
            'receipt_by' => "required|string",
            'receipt_date' => "required|date",
        ];
    }
}
