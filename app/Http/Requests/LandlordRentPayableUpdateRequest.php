<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LandlordRentPayableUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|numeric|exists:landlord_rent_payables,id',
            'payment_method' => 'required|string',
            'item_description' => 'nullable|string',
            'status' => 'required|string',
            'create_date' => 'required|date',
            'is_active' => 'nullable|boolean',
   

            // Related LandlordPayableRents
            'payable_rents' => 'required|array',
            'payable_rents.*.id' => 'nullable|numeric|exists:landlord_payable_rents,id',
            'payable_rents.*.rent_id' => 'required|numeric|exists:rents,id',

            'landlord_id' => 'required|numeric|exists:landlords,id',

            // Related RentAdjustments
            'rent_adjustments' => 'present|array',
            'rent_adjustments.*.id' => 'nullable|numeric|exists:rent_adjustments,id',
            'rent_adjustments.*.amount' => 'required|numeric',
            'rent_adjustments.*.description' => 'nullable|string',
             "rent_adjustments.*.repair_id" => "nullable|numeric|exists:repairs,id",
            "rent_adjustments.*.expense_id" => "nullable|numeric|exists:expenses,id",
            "rent_adjustments.*.files" => "present|array",
            "rent_adjustments.*.files.*" => "string", //

        ];
    }

    public function messages()
    {
        return [
            'payable_rents.*.rent_id.required' => 'Each payable rent must have a rent_id.',
            'rent_adjustments.*.amount.required' => 'Each rent adjustment must have an amount.',
        ];
    }
}
