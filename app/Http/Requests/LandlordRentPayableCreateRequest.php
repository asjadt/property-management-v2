<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LandlordRentPayableCreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'payment_method' => 'required|string',
            'item_description' => 'nullable|string',
            'status' => 'required|string',
            'create_date' => 'required|date',
            'is_active' => 'nullable|boolean',

            // Related LandlordPayableRents
            'payable_rents' => 'required|array',
            'payable_rents.*.rent_id' => 'required|numeric|exists:rents,id',
            
            'landlord_id' => 'required|numeric|exists:landlords,id',

            // Related RentAdjustments
            'rent_adjustments' => 'present|array',
            'rent_adjustments.*.amount' => 'required|numeric',
            'rent_adjustments.*.description' => 'nullable|string',
            "rent_adjustments.*.repair_id" => "nullable|numeric|exists:repairs,id",
            "rent_adjustments.*.expense_id" => "nullable|numeric|exists:expenses,id",

        ];
    }
}
