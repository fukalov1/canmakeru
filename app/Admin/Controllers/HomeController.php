<?php

namespace App\Admin\Controllers;

use App\AdminConfig;
use App\Http\Controllers\Controller;
//use Encore\Admin\Controllers\Dashboard;
use App\Admin\Controllers\MyDashboard;
use App\Protokol;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Yandex\OAuth\OAuthClient;
use Illuminate\Support\Facades\Request;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('Поверка счетчиков.')
            ->description('Управление доступом к хранилищу фотографий и записями о клиентах')
            ->row('')
            ->row(function (Row $row) {

                $row->column(4, function (Column $column) {
                    $column->append(MyDashboard::info());

                });

                $row->column(8, function (Column $column) {
                    if (Admin::user()->roles[0]->slug=='administrator') {
                        $column->append(MyDashboard::refreshToken());
                    }
                    $column->append(MyDashboard::viewDinamic());
                });

//                $row->column(8, function (Column $column) {
//                    $column->append(MyDashboard::dependencies());
//                    $column->append(MyDashboard::viewDinamic());
//
//                });
            });
    }

    public function refreshToken(Request $request) {

        $code = (request()->input('code'));

        if (isset($code)) {
            $client = new OAuthClient(config('YANDEX_CLIENT_ID'), config('YANDEX_PASS'));

            try {
                // осуществляем обмен
                $client->requestAccessToken($code);
            } catch (AuthRequestException $ex) {
                echo $ex->getMessage();
            }

// забираем полученный токен
            $token = $client->getAccessToken();
            AdminConfig::where('name','YANDEX_TOKEN')->update(['value'=>$token]);

            config('YANDEX_TOKEN',$token);
            return json_encode(['message' => 'Токен успешно обновлен!']);
        }
    }

    public function exportImage2YandexDisk() {
        $limit = 1;

        $protokols = new Protokol();
        $data = $protokols->where('protokol_photo','LIKE','%photos%')->take($limit)->get();

        foreach ($data as $protokol) {
            $matches = [];
            preg_match('/(\d\d\d\d)\-(\d\d)/', $protokol->protokol_dt,$matches);
            $result = $protokols->uploadExistFile($protokol->protokol_photo, $matches[1].'-'.$matches[2]);
            if(is_array($result)) {
                if($result['success']) {
                    echo "Upload file ".$protokol->protokol_photo." is successfully.<br/>";
                }
            }
        }

    }

}
