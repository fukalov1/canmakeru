<?php

namespace App\Admin\Controllers;

use App\AdminConfig;
use App\Http\Controllers\Controller;
//use Encore\Admin\Controllers\Dashboard;
use App\Admin\Controllers\MyDashboard;
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

                $row->column(4, function (Column $column) {
                    $column->append(MyDashboard::refreshToken());
                });

                $row->column(4, function (Column $column) {
                    $column->append(MyDashboard::dependencies());
                });
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
}
