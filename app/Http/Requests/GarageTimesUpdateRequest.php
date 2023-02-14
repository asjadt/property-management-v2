<?php

namespace App\Http\Requests;

use App\Rules\TimeValidation;
use Illuminate\Foundation\Http\FormRequest;

class GarageTimesUpdateRequest extends FormRequest
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
            'garage_id' => 'required|numeric',
             "times" => "array",
             "times.*.day" => ["numeric",],
             "times.*.opening_time" => ['required','date_format:H:i:s?', new TimeValidation
            ],
             "times.*.closing_time" => ['required','date_format:H:i:s?', new TimeValidation
            ],

        ];
    }
}