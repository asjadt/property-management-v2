<?php



namespace App\Http\Requests;

use App\Models\Applicant;
use Illuminate\Foundation\Http\FormRequest;

class ApplicantUpdateRequest extends FormRequest
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

            'id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {

                    $applicant_query_params = [
                        "id" => $this->id,
                    ];
                    $applicant = Applicant::where($applicant_query_params)
                        ->first();
                    if (!$applicant) {
                        // $fail($attribute . " is invalid.");
                        $fail("no applicant found");
                        return 0;
                    }
                    if ($applicant->created_by != auth()->user()->id) {
                        // $fail($attribute . " is invalid.");
                        $fail("You do not have permission to update this applicant due to role restrictions.");
                    }
                },
            ],


            'customer_name' => [
                'required',
                'string'
            ],

            'customer_phone' => [
                'required',
                'string'
            ],

            'email' => [
                'required',
                'string',
                'email'
            ],
            'country' => [
                'required',
                'string'
            ],
            'city' => [
                'required',
                'string'
            ],
                'postcode' => [
                'required',
                'string'
            ],


            'min_price' => [
                'required',
                'numeric'
            ],

            'max_price' => [
                'required',
                'numeric'
            ],

            'address_line_1' => [
                'required',
                'string'
            ],

            'latitude' => [
                'nullable',
                'numeric'
            ],

            'longitude' => [
                'nullable',
                'numeric'
            ],

            'radius' => [
                'nullable',
                'numeric'
            ],

            'property_type' => [
                'required',
                'string'
            ],

            'no_of_beds' => [
                'required',
                'string'
            ],

            'no_of_baths' => [
                'required',
                'string'
            ],

            'deadline_to_move' => [
                'nullable',
                'string'
            ],

            'working' => [
                'nullable',
                'string'
            ],

            'job_title' => [
                'nullable',
                'string'
            ],

            'is_dss' => [
                'required',
                'boolean'
            ],

        ];

        return $rules;
    }
}
