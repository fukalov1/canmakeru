<?php

namespace App\Admin\Controllers;

use App\Customer;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
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

    public static function  viewDinamic() {

        $quest  = Customer::join('protokols','customers.id','protokols.customer_id')
            ->select(\DB::raw('date_format(protokols.protokol_dt, "%Y-%m") as date, count(customer_id) count'))
            ->whereRaw('date_format(protokol_dt, "%Y-%m") <> "0000-00"')
            ->groupBy(\DB::raw('date_format(protokol_dt, "%Y-%m")'))
            ->get()->toArray();
//        dd($quest);

        $data = [
            'dinamic' => $quest
        ];

        return view('dinamicDashboard', $data);
    }

}
