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
        return true;
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
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
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
            'end_time.after' => 'The end time must be after the start time.',
            'employee_id.required' => 'The employee name is required.',
            'start_time.date_format' => 'The start time must be in format: Y-m-d H:i:s (e.g., 2025-10-30 14:30:00)',
            'end_time.date_format' => 'The end time must be in format: Y-m-d H:i:s (e.g., 2025-10-30 16:30:00)',
        ];
    }
}
