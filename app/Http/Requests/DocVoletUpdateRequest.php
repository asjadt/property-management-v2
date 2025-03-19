<?php




namespace App\Http\Requests;

use App\Models\DocVolet;
use Illuminate\Foundation\Http\FormRequest;

class DocVoletUpdateRequest extends FormRequest
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

                    $doc_volet_query_params = [
                        "id" => $this->id,
                    ];
                    $doc_volet = DocVolet::where($doc_volet_query_params)
                        ->first();
                    if (!$doc_volet) {
                        // $fail($attribute . " is invalid.");
                        $fail("no doc volet found");
                        return 0;
                    }



                    if ($doc_volet->created_by != auth()->user()->id) {
                        // $fail($attribute . " is invalid.");
                        $fail("You do not have permission to update this doc volet due to role restrictions.");
                    }
                },
            ],



            'title' => [
                'nullable',
                'string'
            ],

            'description' => [
                'nullable',
                'string',

            ],

            'date' => [
                'nullable',
                'string',
            ],

            'note' => [
                'nullable',
                'string'
            ],

            'files' => [
                'present',
                'array'
            ],

            'property_id' => [
                'required',
                'numeric',
                'exists:properties,id'
            ],







        ];



        return $rules;
    }
}
