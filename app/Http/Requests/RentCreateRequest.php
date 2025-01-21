<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;



class RentCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return  bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return  array
     */
    public function rules()
    {

        $rules = [

            'rent_taken_by' => "required|string",
            'remarks' => "required|string",
            'tenancy_agreement_id' => [
                'required',
                'numeric',
                'exists:tenancy_agreements,id'

            ],

            'payment_date' => [
                'required',
                'date',

            ],

            'rent_amount' => [
                'required',
                'numeric',

            ],

            'paid_amount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($value > request()->input('rent_amount')) {
                        $fail('The paid amount must be less than or equal to the rent amount.');
                    }
                },
            ],



            'month' => [
                'required',
                'integer',

            ],

            'year' => [
                'required',
                'integer',

            ],


        ];



        return $rules;
    }
}
