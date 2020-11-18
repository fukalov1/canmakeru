<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * содание транзакции
     */
    public function createTransaction($customer_id, $amount, $count)
    {
        try {
            $transaction = new Transaction;
            $transaction->customer_id = $customer_id;
            $transaction->uuid = $this->GUID();
            $transaction->amount = $amount*$count;
            $transaction->count = $count;
            $transaction->type = 'приход';
            $transaction->status = 1;
            $transaction->RequestId = time();
            $transaction->save();

            $customer = new Customer();
            // пересчет оставшегося лимита на чеки
            $customer->calcLimit($customer_id);

            return $transaction;
        }
        catch (\Throwable $exception) {
            return [
                'response' => 'error',
                'message' => $exception->getMessage()
                ];
        }
    }

    public function setCheckQueueId($id, $CheckQueueId)
    {
        $transaction = Transaction::find($id);
        $transaction->CheckQueueId = $CheckQueueId;
        $transaction->save();
    }


    private function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));

    }
}
