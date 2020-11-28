<?php

namespace App\Http\Controllers\Rest\v1;

use App\Customer;
use App\Http\Controllers\ApiController;
use App\Http\Requests\RestApi;
use App\Lib\KitOnline\KitOnlineService;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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

        $customer = $this->customer
            ->where('code', $request->code)
            ->where('check_online', 1)
            ->first();
        if ($customer) {
            $amount = $request->amount;
            $count = $request->count;

            if ($this->checkLimit(1, $amount * $count)) {
                $transaction = $this->transaction->createTransaction($customer->id, $amount, $count);
                $data = $this->api->sendCheck($transaction);

                if (isset($data->CheckQueueId)) {
                    $this->transaction->setCheckQueueId($transaction->id, $data->CheckQueueId);
                    $data = ['response' => 'success', 'message' => 'CheckQueueId: '.$data->CheckQueueId];
                }
                else {
                    $data = ['response' => 'error', 'message' => 'ResultCode: '.$data->ResultCode];
                }
            } else {
                $data = ['response' => 'error', 'message' => 'Limit is exceeded'];
            }
        }
        else {
            $data = ['response' => 'error', 'message' => 'Partner '.$request->code.' not found!'];
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

    public function listChecks(Request $request)
    {
        $transactions = $this->transaction;

        $transactions = $transactions->where('type',2);

        if ($request->status)
            $transactions = $transactions->where('status', $request->status);

        if ($request->date1 and $request->date2) {
            $transactions = $transactions->whereDate('created_at', '>=', $request->date1);
            $transactions = $transactions->whereDate('created_at', '<=', $request->date2);
        }

        return $transactions->get();
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
