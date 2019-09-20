<?php

namespace App\Http\Controllers;

use App\Protokol;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Passport;
use GuzzleHttp;
use Yandex\Disk\DiskClient;
use Illuminate\Http\Request;
use Yandex\OAuth\OAuthClient;
use App\Customer;
use Illuminate\Support\Facades\Log;

use Yandex\OAuth\Exception\AuthRequestException;


class MainController extends Controller
{

    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        Passport::enableImplicitGrant();
    }

    public function index() {


//        $client = new OAuthClient(config('YANDEX_CLIENT_ID'), config('YANDEX_PASS'));

//            try {
    // осуществляем обмен
//            $client->requestAccessToken('1817294');
//            } catch (AuthRequestException $ex) {
//                echo $ex->getMessage();
//            }

// забираем полученный токен
//        $token = $client->getAccessToken();
//        dd($token);

//        echo '<a href="https://oauth.yandex.ru/authorize?response_type=code&client_id='.$client_id.'">Страница запроса доступа</a>';
//        $client = new OAuthClient(env('YANDEX_CLIENT_ID'));
// сделать редирект и выйти
//        $client->authRedirect(true, OAuthClient::TOKEN_AUTH_TYPE);
//Передать в запросе какое-то значение в параметре state, чтобы Yandex в ответе его вернул
//        $state = 'yandex-php-library';
//        $client->authRedirect(true, OAuthClient::TOKEN_AUTH_TYPE, $state);

        // Формирование параметров (тела) POST-запроса с указанием кода подтверждения
//        $query = array(
//            'grant_type' => 'authorization_code',
//            'code' => 9958708,
//            'client_id' => env('YANDEX_CLIENT_ID'),
//            'client_secret' => env('YANDEX_PASS')
//        );
//        $query = http_build_query($query);
//
//        // Формирование заголовков POST-запроса
//        $header = "Content-type: application/x-www-form-urlencoded";
//
//        // Выполнение POST-запроса и вывод результата
//        $opts = array('http' =>
//            array(
//                'method'  => 'POST',
//                'header'  => $header,
//                'content' => $query
//            )
//        );
//        $context = stream_context_create($opts);
//        $result = file_get_contents('https://oauth.yandex.ru/token', false, $context);
//        $result = json_decode($result);
//
//        // Токен необходимо сохранить для использования в запросах к API Директа
//        echo $result->access_token;
//
//        exit;

//        $guzzle = new GuzzleHttp\Client;
//
//        $response = $guzzle->post('http://your-app.com/oauth/token', [
//            'form_params' => [
//                'grant_type' => 'authorization_code',
//                'client_id' => env('YANDEX_CLIENT_ID'),
//                'client_secret' => env('YANDEX_PASS'),
//                'code' => 9958708
//            ],
//        ]);
//
//        dd($response);
//exit;
//        $disk = new DiskClient();
        //Устанавливаем полученный токен

//        $disk->setAccessToken(env('YANDEX_TOKEN'));
        //Получаем список файлов из директории
//        $files = $disk->directoryContents();
//        $obj = collect($files);
//
//        $dirs = $obj->filter(function ($value, $key) {
//            return $value['resourceType']=='dir' and $value['displayName']=='2019-08-30';
//        });
//
//        dd(count($dirs));


//        foreach($files as $file) {
//            if($file['contentType'] == 'image/jpeg') {
//                echo  $file['href']."<br>";
//                Вывод превьюшки
//                $size = '100x100';
//                $img = $disk->getImagePreview($file['href'], $size);
//                $imgData = base64_encode($img['body']);
//                $path = $file['href'];
//                $type = pathinfo($path, PATHINFO_EXTENSION);
//                $src = 'data: '.mime_content_type($file['displayName']).';base64,'.$imgData;
//                $base64 = 'data:image/'.$type.';base64,' . $imgData;
//                echo '<img src="'.$base64.'">';
//                echo $img['body'];
//            }
//        }

        //Получаем свободное и занятое место/
//        $diskSpace = $disk->diskSpaceInfo();
//        echo "Всего места: ". $diskSpace['availableBytes']."байт.";
//        echo "<br />Использовано: ".$diskSpace['usedBytes']."байт.";
//        echo "<br/>Свободно:".round(($diskSpace['availableBytes'] - $diskSpace['usedBytes']) / 1024 / 1024 / 1024, 2)
//            ."ГБ из ".round($diskSpace['availableBytes'] / 1024 / 1024 / 1024, 2)."ГБ.";


        return view('welcome');

    }

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
                Log::info("Клиент с $code не найден.");
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

        }
        catch (Exception $ex) {
            //Выводим сообщение об исключении.
            $error =  $ex->getMessage();

        }
        return json_encode(['success' => $success, 'message' => $error]);

    }

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
        $disk = new DiskClient();
        //Устанавливаем полученный токен

        $disk->setAccessToken(config('YANDEX_TOKEN'));

        //Вывод превьюшки
        $size = 'XXL';
        $img = $disk->getImagePreview('/'.$year.'-'.$month.'/'.$file, $size);
        header("Content-type: image/jpeg");

        echo $img['body'];

    }

    public function showResult(Request $request)
    {

        $this->validate($request, [
            'id_1' => 'required|max:3',
            'id_2' => 'required|max:2',
            'id_3' => 'required|max:5',
            'pin' => 'required|min:4|max:4',
        ]);

        $data['error'] = 'empty';
        // $nmbr = preg_replace('/\-/', '', request()->post('nmbr'));
        //dd((int)$nmbr);
        $id_1 = request()->post('id_1');
        $id_2 = request()->post('id_2');
        $id_3 = request()->post('id_3');
        // dd($post, $id_1,$id_2,$id_3);

        $prot_id= floatval(str_pad($id_1, 3, "0", STR_PAD_LEFT).str_pad($id_2, 2, "0", STR_PAD_LEFT).str_pad($id_3, 5, "0", STR_PAD_LEFT)); 

        $pin = request()->post('pin');

        // dd((int)$prot_id);
//        dd($pin, (int)($nmbr1.$nmbr2.$nmbr3));

        $protokol = Protokol::where('pin', $pin)
            ->where('protokol_num', (int)$prot_id)
            ->get();
        foreach ($protokol as $item) {
            preg_match('/(\d\d\d\d)\-(\d\d)/', $item->protokol_dt,$matches);
            $file = preg_replace('/photos\//','',$item->protokol_photo);
            $data['protokol_photo'] = $matches[1].'/'.$matches[2].'/'.$file;
            $file = preg_replace('/photos\//','',$item->protokol_photo1);
            $data['protokol_photo1'] = $matches[1].'/'.$matches[2].'/'.$file;
            $file = preg_replace('/photos\//','',$item->meter_photo);
            $data['meter_photo'] = $matches[1].'/'.$matches[2].'/'.$file;
            $data['error'] = '';
            $protokol_num = $item->protokol_num;
            $protokol_formated_num = intval(substr($protokol_num, 0,-7)).'-'.intval(substr($protokol_num, -7,2)).'-'.intval(substr($protokol_num, -5));

            $data['number'] = $protokol_formated_num;
            //request()->post('nmbr');
        }
//        dd($data);

        return view('showResult', $data);
    }

}
