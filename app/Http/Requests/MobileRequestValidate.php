<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Request;

class MobileRequestValidate extends Request
{
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
