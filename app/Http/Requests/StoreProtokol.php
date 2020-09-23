<?php

namespace App\Http\Requests;

use Illuminate\Http\Client\Request;
//use Illuminate\Foundation\Http\FormRequest;

class StoreProtokol extends Request
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
            'partnerKey' => 'required|string',
            'protokol_num' => 'required|numeric',
            'pin' => 'required|numeric',
            'dt' => 'required|string'
        ];
    }
}
