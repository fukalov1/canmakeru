<?php

namespace App;
use Carbon\Carbon;
use Yandex\Disk\DiskClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class Protokol extends Model
{

    public const LOG_CHANNEL = 'customlog';

    protected $fillable = ['protokol_num', 'pin', 'protokol_photo', 'protokol_photo1', 'meter_photo', 'customer_id', 'protokol_dt', 'lat', 'lng',
        'siType', 'waterType', 'regNumber', 'serialNumber', 'checkInterval', 'checkMethod', 'nextTest', 'exported'];

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
        catch (\Throwable $ex) {
            //Выводим сообщение об исключении.
            $success =  $ex->getMessage();
//            Log::channel(self::LOG_CHANNEL)->debug("Экспорт файла $file с ошибкой $success");
            Log::info("Экспорт файла $file с ошибкой $success");
            dd($ex->getMessage());

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
//        $protokols = $this->where('protokol_photo', 'photos/protokol_5ca4a8d858d16.jpg')->get();

        try {
            $disk = new DiskClient();
            //Устанавливаем полученный токен
            $disk->setAccessToken(config('YANDEX_TOKEN'));

            $diskClient = new DiskClient($disk->getAccessToken());
            $diskClient->setServiceScheme(DiskClient::HTTPS_SCHEME);

            $nmbr = 1;
            $i = 0;
            $offer =  1;

            $folder_current='';
            foreach ($protokols as $protokol) {
                $photo = preg_replace('/photos\//', '', $protokol->protokol_photo);
                $photo1 = preg_replace('/photos\//', '', $protokol->protokol_photo1);
                $meter = preg_replace('/photos\//', '', $protokol->meter_photo);

                $old_folder =  (new Carbon($protokol->updated_at))->formatLocalized('%Y-%m');
                $folder =  (new Carbon($protokol->protokol_dt))->formatLocalized('%Y-%m');

                if ($folder != $folder_current) {
                    $folder_current = $folder;
                    $files = $disk->directoryContents($folder);
                    $obj = collect($files);
                }


                if($old_folder != $folder) {
                    echo "$nmbr. Repair files $photo, $photo1, $meter in folder $folder\n";
                    $data = $this->checkFileYaDisk($obj, $photo, $photo1, $meter);
                    if ($data->photo == '') {
                        $this->removePhoto($diskClient, $old_folder, $folder, $photo);
                    }
                    if ($data->photo1 == '') {
                        $this->removePhoto($diskClient, $old_folder, $folder, $photo1);
                    }
                    if ($data->meter == '') {
                        $this->removePhoto($diskClient, $old_folder, $folder, $meter);
                    }

                    if ($i == $offer) {
                        sleep(1);
                        $i = 0;
                    }
                    $i++;
                }
                else {
                    echo "$nmbr. Skip files $photo, $photo1, $meter in folder $folder\n";
                }
                $nmbr++;
            }
        }
        catch (Exception $ex) {
            echo "Error: ".$ex->getMessage();
        }
    }

    private function checkFileYaDisk($obj, $photo, $photo1, $meter)
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

    private function removePhoto($disk, $old_folder, $folder,$filename)
    {
        try {
            if ($disk->move('/'.$old_folder . '/' . $filename, '/'.$folder . '/' . $filename)) {
                echo "Remove lost file /$old_folder/$filename from  to /$folder/$filename\n";
            } else {
                echo "Error remove file  $filename from $old_folder to $folder\n";
            }
        }
        catch (\Exception $ex)
        {
            echo "Error remove file  $filename from $old_folder to $folder: ".$ex->getMessage()."\n";
        }

    }

    public function deleteDublicates()
    {
//        $data = \DB::select('SELECT DISTINCT(protokol_num) protokol_num protokol_photo FROM `protokols`');

        $protokols = $this->orderBy('protokol_num')->orderBy('id', 'desc')->get();
        $protokols1 = new Protokols1();

        $nmbr = '';
        $i=0;
        $skip=0;
        foreach ($protokols as $protokol) {
            if ($nmbr != $protokol->protokol_num) {
                try {
                    $data = $protokol->toArray();
                    array_shift($data);
//                    print_r($data);
//                    exit;
                    Protokols1::insert($data);
//                    $protokols1->save();
                    $i++;
                    $nmbr = $protokol->protokol_num;
                    echo "Insert Number: " . $protokol->protokol_num . " record: $i of " . $protokols->count() . "\n";
//                    exit;
                }
                catch (\Exception $ex) {
                    echo "Error insert record ".$protokol->protokol_num." ".$ex->getMessage()."\n";
                    $skip++;
                }
            }
            else {
                $skip++;
            }
        }
        echo "Final process records! Insert: $i Skip: $skip\n";

    }

}
