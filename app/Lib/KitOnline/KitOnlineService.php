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
        $this->CompanyId = config('KitOnline_CompanyId');
        $this->UserLogin = config('KitOnline_UserLogin');
        $this->Password = config('KitOnline_Password');
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
    public function sendApiRequest($params)
    {
        $curl = new Curl( $this->urlSendCheck );
        $curl->setHttps();
        $curl->setPost( $params );

		$request =  $curl->exec( );
        $response = $this->getResponseRequest($request); // Декодируем ответ

        if (!empty($response->ResultCode) or $response->ResultCode > 0) {
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

        $data = $this->prepareRequest($transaction->uuid);

        $data['Check'] = [
            "CheckId" => $transaction->uuid,
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
                ]
                ]
        ];

        $json = json_encode(
            [
                $data
            ]
        );

        $result = $this->sendApiRequest($json);

        return $result;
    }

    public function stateCheck($transaction)
    {
        $data = $this->prepareRequest($transaction->uuid);
        $data['CheckNumber'] = $transaction->uuid;

        $json = json_encode(
            [
                $data
            ]
        );

        $result = $this->sendApiRequest($json);

        return $result;
    }

    protected function prepareRequest($CheckNumber)
    {
         $data = [
             "CompanyId" => $this->CompanyId,
             "RequestId" => time(),
             "UserLogin" => $this->UserLogin,
             "Sign" => $this->getSign($CheckNumber),
             "RequestSource" => "МС-Ресурс"
         ];
         return ['Request' => $data];
    }

    protected function getSign($CheckNumber)
    {
        return md5($this->CompanyId.$this->Password.$CheckNumber);
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
