<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use GuzzleHttp;
use Yandex\Disk\DiskClient;

class MyDashboard extends Dashboard
{

    public static function  info() {

        $data = [
            'total' => 'не известно',
            'used' => 'не известно',
            'free' => 'не известно'
        ];

        try {
            $disk = new DiskClient();

            $disk->setAccessToken(config('YANDEX_TOKEN'));

            //Получаем свободное и занятое место/
            $diskSpace = $disk->diskSpaceInfo();
            $data = [
                'total' => round($diskSpace['availableBytes'] / 1024 / 1024 / 1024, 2),
                'used' => round($diskSpace['usedBytes'] / 1024 / 1024 / 1024, 2),
                'free' => round(($diskSpace['availableBytes'] - $diskSpace['usedBytes']) / 1024 / 1024 / 1024, 2)
            ];
        }
        catch(\Exception $error) {
            $data = [
                'total' => 'не доступно',
                'used' => 'не досупно',
                'free' => 'не доступно'
            ];
        }

        return view('infoDashboard', $data);
    }

    public static function  refreshToken() {

        $data = [
            'YANDEX_CLIENT_ID' => config('YANDEX_CLIENT_ID'),
            'YANDEX_PASS' => config('YANDEX_PASS'),
            'YANDEX_TOKEN' => config('YANDEX_TOKEN'),
        ];

        return view('serviceDashboard', $data);
    }

}
