<?php




namespace App\Http\Requests;

use App\Models\HolderEntity;
use Illuminate\Foundation\Http\FormRequest;

class HolderEntityUpdateRequest extends FormRequest
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

                    $holder_entity_query_params = [
                        "id" => $this->id,
                    ];
                    $holder_entity = HolderEntity::where($holder_entity_query_params)
                        ->first();
                    if (!$holder_entity) {
                        // $fail($attribute . " is invalid.");
                        $fail("no holder entity found");
                        return 0;
                    }



                    if ($holder_entity->created_by != auth()->user()->id) {
                        // $fail($attribute . " is invalid.");
                        $fail("You do not have permission to update this holder entity due to role restrictions.");
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







        ];



        return $rules;
    }
}
