<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyAgreementCreateRequest extends FormRequest
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
