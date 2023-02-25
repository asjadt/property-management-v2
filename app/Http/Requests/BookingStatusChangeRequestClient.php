<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingStatusChangeRequestClient extends FormRequest
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
            "id" => "required|numeric",
            "status" => "required|string|in:accepted,rejected_by_client", [
                'status.in' => 'The :attribute field must be either "accepted" or "rejected by client".'
            ]
        ];
    }
    public function messages()
    {

        return [
       "status.in" => 'The :attribute field must be either accepted or rejected_by_client.',

        ];
    }
}
