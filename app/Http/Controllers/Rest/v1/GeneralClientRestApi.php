<?php

namespace App\Http\Controllers\Rest\v1;

use App\Customer;
use App\Http\Controllers\ApiController;
use App\Http\Requests\RestApi;
use App\Lib\KitOnline\KitOnlineService;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralClientRestApi extends ApiController
{

    public $customers;
    const LOG_CHANEL = 'rest_api';

    public function __construct(Customer $customer, Transaction $transaction, KitOnlineService $api)
    {
        $this->customer = $customer;
        $this->transaction = $transaction;
        $this->api = $api;
    }

    /**
     * Метод для регистрации онлайн-чека
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCreate(RestApi $request)
    {
        $data = [];
        $validated = $request->validated();
        if (!$this->checkSign($request)) {
            return collect(['response' => 'error', 'message' => 'Signature is invalid']);
        }

        $amount = $request->amount;
        $count = $request->count;

//        dd($this->checkLimit(1, $amount*$count), $amount, $count);
        if ($this->checkLimit(1, $amount*$count)) {
            $transaction = $this->transaction->createTransaction(1, $amount, $count);
            $data = $this->api->sendCheck($transaction);
        }
        else {
            $data = ['response' =>'error', 'message' => 'Limit is exceeded'];
        }

        return $data;
    }

    /**
     * проверка подписи
     */
    private function checkSign($request)
    {
        $key = config('secret_key', '1');
        $result = false;

        $sign = $request->sign;
        unset($request['sign']);
        $params = $request->all();
//dd(http_build_query($params)."&key=".$key, md5(http_build_query($params)."&key=".$key) , $sign);
        if (md5(http_build_query($params)."&key=".$key) == $sign) {
            $result = true;
        }
        return $result;
    }



    /**
     * проверка доступного лимита партнера
     */
    private function checkLimit($id, $amount)
    {
        if ($this->customer->checkLimit($id, $amount)>=0)
            return true;
        else
            return false;
    }

}
