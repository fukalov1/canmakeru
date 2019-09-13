<?php

namespace App;
use Yandex\Disk\DiskClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class Protokol extends Model
{

    public function uploadExistFile($filename, $path)
    {

        $filename = preg_replace('/photos\//', '', $filename);

        $error = '';
        $success = false;


        try {
            $disk = new DiskClient();
            //Устанавливаем полученный токен

            $disk->setAccessToken(env('YANDEX_TOKEN'));

            $files = $disk->directoryContents();
            $obj = collect($files);

            $dirs = $obj->filter(function ($value, $key) use ($path) {
                return $value['resourceType'] == 'dir' and $value['displayName'] == $path;
            });

            // Создаем директорию текущей даты
            if (count($dirs) == 0) {
                $dirContent = $disk->createDirectory($path);
                if ($dirContent) {
                    Log::info( 'Создана новая директория "' . $path . '');
                }
            }
            else {
                echo 'Директория "' . $path . '" выбрана';
            }

            $disk->uploadFile(
                "/$path/",
                array(
                    'path' => public_path('photos/'),
                    'size' => $this->getFileSize(public_path('photos/').$filename),
                    'name' => $filename
                )
            );


            $success = true;

        }
        catch (Exception $ex) {
            //Выводим сообщение об исключении.
//            $error =  $ex->getMessage();
            dd($ex);
            Log::info('Ошибка загрузки файла '.$filename);

        }

        return ['success' => $success, 'message' => $error];

    }

    public function uploadFile($file)
    {
        $error = '';
        $success = false;

//        dd(public_path('photos/temp/').$dest1);

        try {
            $disk = new DiskClient();
            //Устанавливаем полученный токен

            $disk->setAccessToken(config('YANDEX_TOKEN'));

            $files = $disk->directoryContents();
            $obj = collect($files);

            $path = date('Y-m', time());
            $dirs = $obj->filter(function ($value, $key) use ($path) {
                return $value['resourceType'] == 'dir' and $value['displayName'] == $path;
            });

//            print_r($dirs->toArray());
//            dd($path);

            // Создаем директорию текущей даты
            if (count($dirs->toArray()) == 0) {
                $dirContent = $disk->createDirectory($path);
                if ($dirContent) {
//                    echo 'Создана новая директория "' . $path . '"!';
                }
            }
//dd('start upload to yandex disk');

//            dd(['path' => public_path('photos/temp/'),
//                'size' => filesize(public_path('photos/temp/').$dest1),
//                'name' => $dest1]);

            $disk->uploadFile(
                "/$path/",
                array(
                    'path' => public_path('photos/temp/').$file,
                    'size' => filesize(public_path('photos/temp/').$file),
                    'name' => $file
                )
            );

            unlink(public_path('photos/temp/').$file);

            $success = true;

        }
        catch (Exception $ex) {
            //Выводим сообщение об исключении.
            $error =  $ex->getMessage();

        }
        return $success;

    }

    private function getFileSize($filename) {
        return filesize($filename);
    }

}
