<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProtokolRequest extends FormRequest
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
        return [
            'id_1' => 'required|min:1|max:3',
            'id_2' => 'required|min:2|max:2',
            'id_3' => 'required|min:5|max:5',
            'pin' => 'required|min:4|max:4',
        ];
    }

    public function messages()
    {
        return [
            'id_1.required' => 'A title is required',
            'pin.required'  => 'A message is required',
        ];
    }
    
}
