<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiptUpdateRequest extends FormRequest
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
            'id' => "required|numeric|exists:receipts,id",
            'tenant_id' => [
                'nullable',
                'numeric',
                'exists:tenants,id',
                function ($attribute, $value, $fail)  {
                    // Check if 'tenant_id' is not present and 'tenant_name' is also not present
                    if (empty(request()->input('tenant_id')) && empty(request()->input('tenant_name'))) {
                        $fail("Either 'tenant_id' or 'tenant_name' is required.");
                    }
                }
            ],
            'tenant_name' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail)  {
                    // Check if 'tenant_name' is not present and 'tenant_id' is also not present
                    if (empty(request()->input('tenant_name')) && empty(request()->input('tenant_id'))) {
                        $fail("Either 'tenant_id' or 'tenant_name' is required.");
                    }
                }
            ],
            'property_address' => "required|string|exists:properties,address",
            'amount' => "required|numeric",
            'receipt_by' => "nullable|string",
            'receipt_date' => "required|date",
            'notes' => "nullable|string",
            'payment_method' => "required|string",


            "sale_items" => "nullable|array",
            "sale_items.*.sale_id" => "required|numeric|exists:sale_items,id",
            "sale_items.*.item" => "required|string",
            "sale_items.*.description" => "nullable|string",
            "sale_items.*.amount" => "required|numeric",
        ];
    }
}
