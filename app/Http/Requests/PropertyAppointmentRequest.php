<?php

namespace App\Http\Requests;

use App\Rules\ValidateAppointment;
use App\Rules\ValidProperty;
use Illuminate\Foundation\Http\FormRequest;

class PropertyAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'job_type' => 'required|string|max:255',
            'employee_id' => 'required|string|max:255',
            'start_date' => 'required|date|date_format:d-m-Y',
            'end_date' => 'required|date|date_format:d-m-Y|after_or_equal:start_date',
            'description' => 'nullable|string',
            'property_id' => ['nullable', 'integer', new ValidProperty()],
        ];

        // If the request is for updating an appointment, we can add additional rules
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['id'] = ['required', 'integer', new ValidateAppointment()];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'end_date.after_or_equal' => 'The end date must be equal to or after the start date.',
        ];
    }
}
