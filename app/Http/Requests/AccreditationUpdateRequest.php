<?php





namespace App\Http\Requests;

use App\Models\Accreditation;
use Illuminate\Foundation\Http\FormRequest;

class AccreditationUpdateRequest extends FormRequest
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

                    $accreditation_query_params = [
                        "id" => $this->id,
                    ];
                    $accreditation = Accreditation::where($accreditation_query_params)
                        ->first();
                    if (!$accreditation) {
                        // $fail($attribute . " is invalid.");
                        $fail("no accreditation found");
                        return 0;
                    }



                    if ($accreditation->created_by != auth()->user()->id) {
                        // $fail($attribute . " is invalid.");
                        $fail("You do not have permission to update this accreditation due to role restrictions.");
                    }
                },
            ],



            'name' => [
                'required',
                'string',

            ],

            'description' => [
                'nullable',
                'string',

            ],

            'accreditation_start_date' => [
                'required',
                'date',

            ],

            'accreditation_expiry_date' => [
                'required',
                'date',

            ],

            'logo' => [
                'nullable',
                'string',

            ],

            'property_id' => [
                'required',
                'numeric',
                'exists:properties,id'

            ]


        ];



        return $rules;
    }
}
