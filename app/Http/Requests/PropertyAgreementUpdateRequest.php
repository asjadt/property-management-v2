<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyAgreementUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|numeric',
            'landlord_id' => 'required|exists:landlords,id',
            'property_id' => 'required|exists:properties,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_arrangement' => 'required|in:By_Cash,By_Cheque,Bank_Transfer',
            'cheque_payable_to' => 'required|string',
            'agent_commision' => 'required|numeric|min:0',
            'terms_conditions' => 'required|string',
        ];
    }
}
