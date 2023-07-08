<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRegisterBusinessRequest extends FormRequest
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
            'user.first_Name' => 'required|string|max:255',
            'user.last_Name' => 'required|string|max:255',
            'user.email' => 'required|string|email|max:255|unique:users,email',
            'user.password' => 'required|confirmed|string|min:6',
            'user.phone' => 'required|string',
            'user.image' => 'nullable|string',

            'business.name' => 'required|string|max:255',
            'business.about' => 'nullable|string',
            'business.web_page' => 'nullable|string',
            'business.phone' => 'nullable|string',

            'business.email' => 'required|string|email|max:255|unique:businesses,email',
            'business.additional_information' => 'nullable|string',

            'business.lat' => 'nullable|string',
            'business.long' => 'nullable|string',
            'business.country' => 'required|string',
            'business.city' => 'required|string',

            'business.currency' => 'nullable|string',

            'business.postcode' => 'required|string',
            'business.address_line_1' => 'required|string',
            'business.address_line_2' => 'nullable|string',


            'business.logo' => 'nullable|string',

            'business.image' => 'nullable|string',

            // 'business.images' => 'nullable|array',
            // 'business.images.*' => 'nullable|string',








        ];

    }


    public function customRequiredMessage($property) {

        return "The ".$property." must be required";
    }

    public function messages()
    {

        return [
            'user.first_Name.required' => $this->customRequiredMessage("first name"),
            'user.last_Name.required' => $this->customRequiredMessage("last name"),
            'user.email.required' => $this->customRequiredMessage("email"),
            'user.password.required' => $this->customRequiredMessage("password"),




            'business.name.required' => $this->customRequiredMessage("business name"),
            'business.email.required' => $this->customRequiredMessage("business email"),
            'business.country.required' => $this->customRequiredMessage("business country"),
            'business.city.required' => $this->customRequiredMessage("business city"),
            'business.postcode.required' => $this->customRequiredMessage("business postcode"),
            'business.address_line_1.required' => $this->customRequiredMessage("business address line 1"),

        ];
    }

}
