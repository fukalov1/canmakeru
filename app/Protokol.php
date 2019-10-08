<?php

namespace App;
use Carbon\Carbon;
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

    public function refreshPhotos($start,$offset)
    {
        echo "Start process record in $start to $offset\n";

        $protokols = $this->skip($start)->take($offset)->get();
        try {
            $disk = new DiskClient();
            //Устанавливаем полученный токен
            $disk->setAccessToken(config('YANDEX_TOKEN'));

            $nmbr = 1;
            $i = 0;
            $offer =  1;

            foreach ($protokols as $protokol) {
                $photo = preg_replace('/photos\//', '', $protokol->protokol_photo);
                $photo1 = preg_replace('/photos\//', '', $protokol->protokol_photo1);
                $meter = preg_replace('/photos\//', '', $protokol->meter_photo);

                $folder =  (new Carbon($protokol->updated_dt))->formatLocalized('%Y-%m');
//                echo " - $folder - $photo - $photo1 - $meter\n";
                $files = $disk->directoryContents($folder);
                $obj = collect($files);

                echo "$nmbr. Checking files $photo, $photo1, $meter in folder $folder\n";
                $data = $this->checkFileYaDisk($obj,$folder,$photo,$photo1,$meter);
                if ($data->photo == '') {
                    $this->reloadPhoto($disk, $folder, $photo);
                }
                if ($data->photo1 == '') {
                    $this->reloadPhoto($disk, $folder, $photo1);
                }
                if ($data->meter == '') {
                    $this->reloadPhoto($disk, $folder, $meter);
                }

                if ($i==$offer) {
                    sleep(1);
                    $i=0;
                }
                $i++;
                $nmbr++;
            }
        }
        catch (Exception $ex) {
            echo "Error: ".$ex->getMessage();
        }
    }

    private function checkFileYaDisk($obj, $folder, $photo, $photo1, $meter)
    {
        $data = collect([]);

        $data->photo = $this->findFile($obj, $photo);
        $data->photo1 = $this->findFile($obj, $photo1);
        $data->meter = $this->findFile($obj, $meter);

        return $data;
    }

    private function findFile($obj,$filename)
    {
        $result = $obj->search(function ($item) use ($filename) {
            return $item['displayName'] == $filename;
        });
        if($result==false) {
            $result = "";
//           echo "File $filename not found!\n";
        }
        else {
            $result = $filename;
//            echo "File $filename exist!\n";
        }
        return $result;

    }

    private function reloadPhoto($disk,$folder,$filename)
    {
        echo "Reload lost file $folder - $filename\n";
    }

}
