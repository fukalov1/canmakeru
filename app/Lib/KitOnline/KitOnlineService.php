<?php
/** Сервис для работы с АПИ Кит онлайн */

namespace App\Lib\KitOnline;

use App\Lib\KitOnline\Curl;
use App\Exceptions\ThrowableCustom;


class KitOnlineService
{

    protected $client; // Guzzle
    protected $CompanyId; // идентификатор компании из личного кабинета.
    protected $UserLogin; //логин пользователя с правами «API» от имени которого выполняется запрос.
    protected $Password;
    protected $FiscalData; //Если указано «1» в запросе статуса чека, в ответе будут возвращены фискальные данные чека
    protected $Link; // Если указано «1» в запросе статуса чека, в ответе будет возвращена ссылка на web-ресурс, отображающий кассовый чек
    protected  $QRCode;


    public function __construct()
    {
        $this->urlSendCheck = 'https://api.kit-invest.ru/WebService.svc/SendCheck';
        $this->urlStateCheck = 'https://api.kit-invest.ru/WebService.svc/StateCheck';
        $this->CompanyId = config('KitOnline_CompanyId', '15822');
        $this->UserLogin = config('KitOnline_UserLogin', 'pinserver');
        $this->Password = config('KitOnline_Password', 'w783uer67hH');
        $this->FiscalData = config('KitOnline_FiscalData', 1);
        $this->Link = config('KitOnline_Link', 1);
        $this->QRCode = config('KitOnline_QRCode', 1);
    }

    /**
     * Функция для вызова методов в системе KitOnline
     * @param $params array
     * @return mixed
     * @throws Exception
     */
    public function sendApiRequest($params, $type = 'send')
    {
        $url = $type == 'send' ? $this->urlSendCheck : $this->urlStateCheck;
        $curl = new Curl( $url );
        $curl->setHttps();
        $curl->setPost( $params );

		$request =  $curl->exec( );
        $response = $this->getResponseRequest($request); // Декодируем ответ
        if (!isset($response->ResultCode) and $response->ResultCode > 0) {
            $response = ['response' => 'error', 'message' => $response->ErrorMessage];
        }

        return $response;
    }

    /**
     * @return mixed
     * Запрос используется для отправки сервису Kit Online кассового чека для фискализации в
    ККТ.
     */
    public function sendCheck($transaction)
    {

        $data = $this->prepareRequest($transaction);

        $data['Check'] = [
            "CheckId" => $transaction->id,
            "CalculationType" =>  1,
            "Sum" =>  $transaction->amount,
            "customer" => $transaction->customer->name,
            "Email" => $transaction->customer->email,
            "Pay" => [
                "CashSum" => $transaction->amount
                ],
            "Subjects" => [
                [
                    "Price" => $transaction->amount/$transaction->count,
                    "Quantity" => $transaction->count,
                    "SubjectName" => "Поверка счетчика",
                    "Tax" => 6
                ]
                ]
        ];

        $json = json_encode(

                $data

        );

//        dd($json);
        $result = $this->sendApiRequest($json);

        return $result;
    }

    public function stateCheck($transaction)
    {
        $data = $this->prepareRequest($transaction);
        $data['CheckQueueId'] = $transaction->CheckQueueId;

        $json = json_encode(
            $data
        );
        $result = $this->sendApiRequest($json, 'state');
        return $result;
    }

    protected function prepareRequest($transaction)
    {
         $data = [
             "CompanyId" => intval($this->CompanyId),
             "RequestId" => $transaction->RequestId,
             "UserLogin" => $this->UserLogin,
             "Sign" => $this->getSign($transaction),
             "RequestSource" => "МС-Ресурс"
         ];
         return ['Request' => $data];
    }

    protected function getSign($transaction)
    {
        $CheckNumber = sprintf("1%010d%02d", $transaction->id , 1);
//        dd(md5($this->CompanyId.$this->Password.$CheckNumber),$this->CompanyId,$this->Password,$CheckNumber);
        return md5($this->CompanyId . $this->Password . $transaction->RequestId);
    }

    /**
     * Метод для декодирования ответа от сервера Payeer
     * @param $request
     * @return mixed
     */
    protected function getResponseRequest($request)
    {
        return json_decode($request);
    }


}
