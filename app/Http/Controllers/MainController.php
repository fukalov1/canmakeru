<?php

namespace App\Http\Controllers;

use App\Act;
use App\Protokol;
use Intervention\Image\Facades\Image as Imagez;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Passport;
use GuzzleHttp;
use Yandex\Disk\DiskClient;
use Illuminate\Http\Request;
use Yandex\OAuth\OAuthClient;
use App\Customer;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

use Yandex\OAuth\Exception\AuthRequestException;


class MainController extends Controller
{

    private $acts;

    public function __construct(Act $acts) {
        $this->acts = $acts;
        // Log::useFiles(storage_path('/logs/').'poverka.log');
        // $logFile = 'poverka.log';
        // Log::getLogger(storage_path().'/logs/'.$logFile);
    }

    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        Passport::enableImplicitGrant();
    }

    public function index() {
        return view('main');
    }

    /**
     * @return false|string
     * получение информации о использовании яндекс диска
     */
    public function getSpaceDisk() {

        $guzzle = new GuzzleHttp\Client;

        $response = $guzzle->post('http://your-app.com/oauth/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => env('YANDEX_CLIENT_ID'),
                'client_secret' => env('YANDEX_PASS'),
                'scope' => 'your-scope',
            ],
        ]);

        $disk = new DiskClient();

        $disk->setAccessToken(env('YANDEX_TOKEN'));

        //Получаем свободное и занятое место/
        $diskSpace = $disk->diskSpaceInfo();
        $data = [
            'total' => round($diskSpace['availableBytes']/1024/1024/1024, 2),
            'used' => round($diskSpace['usedBytes']/1024/1024/1024, 2),
            'free' => round(($diskSpace['availableBytes'] - $diskSpace['usedBytes']) / 1024 / 1024 / 1024, 2)
                ];

        return json_encode($data);

    }


    public function saveResultMeter(Request $request) {

        if ($request->input("appUUID") != '437447dcb8b8') {
            die ("Загрузка невозможна");
        }
        else {

//            dd(request()->post());
            $code = request()->post('partnerKey');
            $customer = Customer::where('code', $code)->where('enabled', 1)->get();
            $protokol = new Protokol();

//            dd($code, $customer);

            if (count($customer) > 0) {
                $photo = 'protokol_' . uniqid() . '.jpg';
                $photo1 = 'protokol1_' . uniqid() . '.jpg';
                $meter = 'meter_' . uniqid() . '.jpg';
                foreach ($customer as $item) {
//                    dd($photo, $photo1, $meter);
                    $protokol->uploadFile($_FILES, $photo, $photo1, $meter);
                }
            } else {
                Log::channel('customlog')->info("Клиент с $code не найден.");
            }
        }
    }

    public function uploadFile()
    {

        //$file = $request->file('file');
        $error = '';
        $success = false;


        try {
            $disk = new DiskClient();
            //Устанавливаем полученный токен

            $disk->setAccessToken(env('YANDEX_TOKEN'));

            $files = $disk->directoryContents();
            $obj = collect($files);

            $path = date('Y-m-d', time());
            $dirs = $obj->filter(function ($value, $key) use ($path) {
                return $value['resourceType'] == 'dir' and $value['displayName'] == $path;
            });

            // Создаем директорию текущей даты
            if (count($dirs) == 0) {
                $dirContent = $disk->createDirectory($path);
                if ($dirContent) {
                    echo 'Создана новая директория "' . $path . '"!';
                }
            }


//            dd($file);

            $disk->uploadFile(
                "/$path/",
                array(
                    'path' => $_FILES['file']['tmp_name'],
                    'size' => $_FILES['file']['size'],
                    'name' => $_FILES['file']['name']
                )
            );

            $success = true;
            Log::channel('customlog')->info('Success export file '.$_FILES['file']['name']);

        }
        catch (Exception $ex) {
            //Выводим сообщение об исключении.
            $error =  $ex->getMessage();
            Log::channel('customlog')->error('Error export file '.$_FILES['file']['name'].' : '.$error);
        }
        return json_encode(['success' => $success, 'message' => $error]);

    }


    /**
     * @param Request $request
     */
    public function showAct(Request $request)
    {
        $id = $request->id;
        $pin = $request->pin;

        if (isset($id) and isset($pin)) {
            $act = $this->acts
                ->where('number_act', $id)
                ->where('pin', $pin)
                ->first();
            if ($act) {
                return view('actPDF', ['act' => $act]);
            }
            else {
                return response('Акт не найден', 404);
            }
        }
        else {
            return response('Акт не найден', 404);
        }
    }


    /**
     * @param string $year
     * @param string $month
     * @param string $file
     */
   public function getPreview($year='2019',$month='01',$file='')
    {
        $disk = new DiskClient();
        //Устанавливаем полученный токен

        $disk->setAccessToken(config('YANDEX_TOKEN'));

        //Вывод превьюшки
        $size = '250';
        $img = $disk->getImagePreview('/'.$year.'-'.$month.'/'.$file, $size);
        header("Content-type: image/jpeg");

        echo $img['body'];

    }

   public function getMiddlePhoto($year='2019',$month='01',$file='')
    {
        $disk = new DiskClient();
        //Устанавливаем полученный токен

        $disk->setAccessToken(config('YANDEX_TOKEN'));

        //Вывод превьюшки
        $size = 'M';
        $img = $disk->getImagePreview('/'.$year.'-'.$month.'/'.$file, $size);
        header("Content-type: image/jpeg");

        echo $img['body'];

    }

   public function getPhoto($year='2019',$month='01',$file='')
    {
        $width = 768;
        $height = 1024;
        $disk = new DiskClient();
        //Устанавливаем полученный токен

        $disk->setAccessToken(config('YANDEX_TOKEN'));

        //Вывод превьюшки
        $size = 'x1024';
        $img = $disk->getImagePreview('/'.$year.'-'.$month.'/'.$file, $size);
        header("Content-type: image/jpeg");

        try {
            $i = Image::make($img['body']);
            $w = $i->width();
            $h = $i->height();

            if ($w/$h > $width/$height) {
                $i->resize(round($height*$w/$h,0), $height);
            }
            else {
                $i->resize($width, round($width*$h/$w,0));
            }
            if ($w>$h) {
                $i->rotate(90);
                $w=$w+$h-$h=$w;
            }
            if ($w/$h<0.7) {
                $i->crop(768, 1280);
            }
            echo $i->stream();
        }
        catch (\Exception $exception) {
            echo null;
        }
    }

   public function getPhoto4Pdf($year='2019',$month='01',$file='')
    {
        $disk = new DiskClient();
        //Устанавливаем полученный токен

        $disk->setAccessToken(config('YANDEX_TOKEN'));

        //Вывод превьюшки
        $size = '330x495';
        $img = $disk->getImagePreview('/'.$year.'-'.$month.'/'.$file, $size);
        header("Content-type: image/jpeg");

        echo $img['body'];

    }

    public function showResult(Request $request)
    {

        $this->validate($request, [
            'id_1' => 'required|between:1,3',
            'id_2' => 'required|between:1,2',
            'id_3' => 'required|between:1,5',
            'pin' => 'required|digits:4',
        ],
        [
            'id_1.required' => 'Первое поле (код клиента) должно быть заполнено',
            'id_2.required' => 'Второе поле (год) должно быть заполнено',
            'id_3.required' => 'Третье поле номер свидетельсва должно быть заполнено',
            'pin.required'  => 'ПИН-код должен быть заполнен',
        ]);


        $data['error'] = 'empty';
        // $nmbr = preg_replace('/\-/', '', request()->post('nmbr'));
        //dd((int)$nmbr);
        $id_1 = request()->post('id_1');
        $id_2 = request()->post('id_2');
        $id_3 = request()->post('id_3');
        // dd($post, $id_1,$id_2,$id_3);

        if ($id_2==20) {
            $prot_id = floatval(str_pad($id_1, 3, "0", STR_PAD_LEFT) . str_pad($id_2, 2, "0", STR_PAD_LEFT) . str_pad($id_3, 5, "0", STR_PAD_LEFT));

            $pin = request()->post('pin');

            // dd((int)$prot_id);
//        dd($pin, (int)($nmbr1.$nmbr2.$nmbr3));

            $protokol = Protokol::where('pin', $pin)
                ->where('protokol_num', (int)$prot_id)
                ->get();
            foreach ($protokol as $item) {
                preg_match('/(\d\d\d\d)\-(\d\d)/', $item->protokol_dt, $matches);
                $file = preg_replace('/photos\//', '', $item->protokol_photo);
                $data['protokol_photo'] = $matches[1] . '/' . $matches[2] . '/' . $file;
                $file = preg_replace('/photos\//', '', $item->protokol_photo1);
                $data['protokol_photo1'] = $matches[1] . '/' . $matches[2] . '/' . $file;
                $file = preg_replace('/photos\//', '', $item->meter_photo);
                $data['meter_photo'] = $matches[1] . '/' . $matches[2] . '/' . $file;
                $data['error'] = '';
                $protokol_num = $item->protokol_num;
                $protokol_formated_num = intval(substr($protokol_num, 0, -7)) . '-' . intval(substr($protokol_num, -7, 2)) . '-' . intval(substr($protokol_num, -5));

                $data['number'] = $protokol_formated_num;
                //request()->post('nmbr');
            }
//        dd($data);

            return view('showResult', $data);
        }
        else {

            $request->id = "$id_1-$id_2-$id_3";
            return $this->showAct($request);
        }
    }

}
