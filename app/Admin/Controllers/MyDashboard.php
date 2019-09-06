<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use GuzzleHttp;
use Yandex\Disk\DiskClient;

class MyDashboard extends Dashboard
{

    public static function  info() {

        $guzzle = new GuzzleHttp\Client;

        $response = $guzzle->post('http://your-app.com/oauth/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => config('YANDEX_CLIENT_ID'),
                'client_secret' => config('YANDEX_PASS'),
                'scope' => 'your-scope',
            ],
        ]);

        $disk = new DiskClient();

        $disk->setAccessToken(config('YANDEX_TOKEN'));

        //Получаем свободное и занятое место/
        $diskSpace = $disk->diskSpaceInfo();
        $data = [
            'total' => round($diskSpace['availableBytes']/1024/1024/1024, 2),
            'used' => round($diskSpace['usedBytes']/1024/1024/1024, 2),
            'free' => round(($diskSpace['availableBytes'] - $diskSpace['usedBytes']) / 1024 / 1024 / 1024, 2)
        ];

        return view('infoDashboard', $data);
    }

    public static function  refreshToken() {

        return view('serviceDashboard');
    }

}
