<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Http\Requests\ActDataRequest;
use App\Act;
use App\Protokol;
use Illuminate\Http\Request;
use App\Http\Requests\ActRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProtokolController extends Controller
{
    public const LOG_CHANNEL = 'customlog';

    public function __construct(Customer $customer, Act $act, Protokol $protokol)
    {
        $this->customer = $customer;
        $this->protokol = $protokol;
        $this->act = $act;
        $this->log = self::LOG_CHANNEL;
    }

    public function uploadData(ActRequest $request)
    {
        $request->validated();

        $rules = [
            'act'=>'required',
            'act.partnerKey'=>'required',
            'act.number_act'=>'required|string',
            'act.pin'=>'required|numeric',
            'act.date'=>'required|date',
            'act.type'=>'required|string',
//            'meters'=>'required_if:act.type,value',
        ];


        $validator = Validator::make(json_decode($request->input('data'), true), $rules);

//        $validator->sometimes('meters', 'required', function($data){
//            return $data->type !== 'брак';
//        });

        if ($validator->fails()) {
            return response(
                json_encode([
                    'result' => 3,
                    'message' => json_encode($validator->errors())
                ]));
        }

        $data = json_decode($request->input('data'));

        // get Customer
        $customer = $this->customer
            ->where('code', $data->act->partnerKey)
            ->where('enabled',1)
            ->first();

        if($customer) {

            $uid = uniqid();
            $uploaddir = 'photos/temp/';
            $act_photo = 'act_'.$data->act->number_act.'.jpg';
            $m_photo = 'meter_'.$uid.'.jpg';

            $files = $this->checkLoadPhoto($request, $data->act->number_act);
            if (count($files)==0) {
                return json_encode([
                    'result' => 2,
                    'message' => 'Ошибка загрузки файлов! '
                ]);
            }

            $lat = isset($data->act->lat) ? $data->act->lat : 0;
            $lng = isset($data->act->lng) ? $data->act->lng : 0;
            $address = isset($data->act->address) ? $data->act->address : 0;
            $act = 0;
            if ($act = $this->act->updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'number_act' => $data->act->number_act,
                    'pin' => $data->act->pin
                ],
                [
                    'date' => $data->act->date,
                    'lat' => $lat,
                    'lng' => $lng,
                    'type' => $data->act->type,
                    'address' => $address,
                ])) {
                    if ($act->id > 0) {
                        $this->addProtokol($act->id, $customer->id, $data);
                    }
                    $this->exportPhoto($files);
                }

            return json_encode([
                'result' => 0,
                'message' => 'успех'
            ]);
        }
        else {
            Log::channel($this->log)->debug("The partner with code {$data->act->partnerKey} not found!");
            return response(
                json_encode([
                    'result' => 1,
                    'message' => "Партнер с кодом \'{$data->act->partnerKey}\' не найден!"
                ]));
        }
    }

    private function addProtokol($act_id, $customer_id, $data)
    {
        $i=1;
        foreach ($data->meters as $meter) {

            $this->protokol->updateOrCreate(
                [
                    'customer_id' => $customer_id,
                    'protokol_num' => $data->act->number_act."-$i",
                    'pin' => $data->act->pin,
                    'act_id' => $act_id
                ],
                [
                    'protokol_dt' => $data->act->date,
                    'meter_photo' => 'meter_'.$data->act->number_act."-$i.jpg",
                    'siType' => $meter->siType,
                    'waterType' => $meter->waterType,
                    'regNumber' => $meter->regNumber,
                    'serialNumber' => $meter->serialNumber,
                    'checkInterval' => $meter->checkInterval,
                    'checkMethod' => $meter->checkMethod,
                    'nextTest' => $this->getNextTest($data->act->date, $meter->checkInterval)
                ]
            );
            $i++;
        }
    }

    public function exportXml2Fgis(Request $request)
    {


        $date = date('Y-m-d', time());
//        $headers = array(
//            'Content-Type' => 'text/xml',
//            'Content-Disposition' => 'attachment; filename="poverka'.$date.'.xml"',
//        );

        $protokols = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<gost:application xmlns:gost=\"urn://fgis-arshin.gost.ru/module-verifications/import/2020-04-14\">\n";

        // подготовливаем xml по результатам поверок
        $protokols .= $this->protokol->prepareData($request->customer_id, $request->package_number);

        $protokols .= "</gost:application>";

        $filename = "poverka_{$date}_".time().".xml";
        Storage::disk('temp')->put($filename, $protokols);

        $package_number = Customer::find(Auth::user()->id)->package_number;

        return json_encode(['filename' => $filename, 'package_number' => $package_number]);

//        return response()->stream(function () use ($protokols)  {
//            echo $protokols;
//        }, 200, $headers);

    }

    private function checkLoadPhoto($request, $number)
    {
        $result = [];
        try {
            if($request->file()) {
                $request->file('act_photo')->move(public_path('/photos/temp/'), "act_$number.jpg");
                $result[] = "act_$number.jpg";

                if($request->hasfile('meter_photos'))
                {
                    $i=1;
                    foreach($request->file('meter_photos') as $file)
                    {
                        $name = "meter_$number-$i.jpg";
                        $file->move(public_path('/photos/temp/'), $name);
                        $i++;
                        $result[] = $name;
                    }
                }

            }
        }
        catch (FileException $exception) {
            report($exception);
            return [];
        }
        return $result;
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
            Log::channel($this->log)->info("Экспорт файла $file: $result");
        }
    }

}
