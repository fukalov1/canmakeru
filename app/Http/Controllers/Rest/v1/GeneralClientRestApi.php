<?php

namespace App\Http\Controllers\Rest\v1;

use App\Customer;
use App\Http\Controllers\ApiController;
use App\Http\Requests\RestApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralClientRestApi extends ApiController
{

    public $customers;
    const LOG_CHANEL = 'rest_api';

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;

    }

    /**
     * Метод для регистрации онлайн-чека
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCreate(RestApi $request)
    {
        $validated = $request->validated();
        if (!$this->checkSign($request)) {
            return collect(['response' => 'error', 'message' => 'Signature is invalid']);
        }

        return $this->customer->find(1);
    }

    private function checkSign($request)
    {
        $key = config('secret_key', '1');
        $result = false;

        $sign = $request->sign;
        unset($request['sign']);
        $params = $request->all();

        if (md5(json_encode($params).$key) == $sign) {
            $result = true;
        }
        return $result;
    }

}
