<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Http\Requests\StoreProtokol;
use App\Protokol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Redirect,Response,File;
use Exception;
//use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class ProtokolController extends Controller
{

    public const LOG_CHANNEL = 'customlog';

    public function __construct(Customer $customer, Protokol $protokol)
    {
        $this->customer = $customer;
        $this->protokol = $protokol;
        $this->log = self::LOG_CHANNEL;
    }

    public function uploadData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'partnerKey' => 'required|string',
            'protokol_num' => 'required|numeric',
            'pin' => 'required|numeric',
            'dt' => 'required|string',
            'protokol_photo' => 'required',
            'protokol_photo1' => 'required',
            'meter_photo' => 'required'
        ]);
        if ($validator->fails()) {
            return response(
                json_encode([
                    'result' => 1,
                    'message' => json_encode($validator->errors())
                ]));
        }

        $partnerKey = $request->input('partnerKey');
        $protokol_num = $request->input('protokol_num');
        $pin = $request->input('pin');
        $protokol_dt = $request->input('dt');
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        $siType = $request->input('siType'); // - тип СИ
        $waterType = $request->input('waterType'); //- Тип воды
        $regNumber = $request->input('regNumber'); // - регистрационный номер
        $serialNumber = $request->input('serialNumber'); // - заводской номер
        $checkInterval = $request->input('checkInterval'); // - интервал поверки
        $checkMethod = $request->input('checkMethod'); // - методика поверки


//        dd('test',$request->file('protokol_photo'));

        // get Customer
        $customer = $this->customer
            ->where('code', $partnerKey)
            ->where('enabled',1)
            ->first();

        if($customer) {

            $uid = uniqid();
            $uploaddir = 'photos/temp/';
            $p_photo = 'protokol_'.$uid.'.jpg';
            $p_photo1 = 'protokol1_'.$uid.'.jpg';
            $m_photo = 'meter_'.$uid.'.jpg';

            $result = $this->checkLoadPhoto($request, $p_photo, $p_photo1, $m_photo);
            if ($result!==true) {
                return json_encode([
                    'result' => 2,
                    'message' => 'Error loading files! '.$result
                ]);
            }

            if ($this->protokol->updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'protokol_num' => $protokol_num,
                    'pin' => $pin
                ],
                [
                    'protokol_photo' => $p_photo,
                    'protokol_photo1' => $p_photo1,
                    'meter_photo' => $m_photo,
                    'protokol_dt' => $protokol_dt,
                    'lat' => $lat,
                    'lng' => $lng,
                    'siType' => $siType,
                    'waterType' => $waterType,
                    'regNumber' => $regNumber,
                    'serialNumber' => $serialNumber,
                    'checkInterval' => $checkInterval,
                    'checkMethod' => $checkMethod,
                    'nextTest' => $this->getNextTest($protokol_dt, $checkInterval)
                ])) {

                $this->exportPhoto([$p_photo, $p_photo1, $m_photo]);

            }

            return json_encode([
                'result' => 0,
                'message' => 'success'
            ]);
        }
        else {
            Log::channel($this->log)->debug("Партнер $partnerKey не найден.");
            return response(
                json_encode([
                    'result' => 1,
                    'message' => 'Партнер не найден'
                ]));
        }
    }

    private function checkLoadPhoto($request, $p, $p1, $m)
    {
        try {
            if($request->file()) {
                $request->file('protokol_photo')->storeAs('temp', $p, 'photos');
                $request->file('protokol_photo1')->storeAs('temp', $p1, 'photos');
                $request->file('meter_photo')->storeAs('temp', $m, 'photos');
            }
        }
        catch (FileException $exception) {
            report($exception);
            return $exception->getMessage();
        }
        return true;
    }


    private function getNextTest($date, $checkInterval)
    {
        $result = null;
        if ((int)$checkInterval > 0) {
            $result = strtotime("+$checkInterval YEAR", strtotime($date));
            $result = strtotime('-1 DAYS', $result);
            $result = date("Y-m-d H:i:s", $result);
        }

        return $result;

    }

    private function exportPhoto($files=[])
    {
        foreach ($files as $file) {
            $output = $this->protokol->uploadFile($file);
            $result = $output===true ? "Export files $file to Yandex.disk successfully" : "Don't export file $file. Error: $output";
            Log::channel($this->log)->debug("Экспорт файла $file: $result");
        }
    }

}
