<?php

namespace App;

use Carbon\Carbon;
use Yandex\Disk\DiskClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;


class Protokol extends Model
{

    protected $fillable = ['act_id', 'customer_id', 'protokol_num', 'pin', 'meter_photo', 'exported', 'protokol_dt',
        'siType', 'waterType', 'regNumber', 'serialNumber', 'checkInterval', 'checkMethod', 'nextTest'];

    const UPDATED_AT = 'updated_dt';


    public function act()
    {
        return $this->belongsTo(Act::class);
    }

    /**
     * @param $filename
     * @param $path
     * @return array
     */
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

    /**
     * функция загрузки файлов на Яндекс.Диск
     * @param $file загружаемый файл
     * @param $date дата акта (св-ва)
     * @return bool
     */
    public function uploadFile($file, $date)
    {
        $error = '';
        $success = false;

        if (isset($date)) {
            $path = date('Y-m', strtotime($date));
        }
        else {
            $path = date('Y-m', time());
        }

        try {
            $disk = new DiskClient();

            //Устанавливаем полученный токен
            $disk->setAccessToken(config('YANDEX_TOKEN'));

            $files = $disk->directoryContents();
            $obj = collect($files);

            $dirs = $obj->filter(function ($value, $key) use ($path) {
                return $value['resourceType'] == 'dir' and $value['displayName'] == $path;
            });

            // Создаем директорию текущей даты
            if (count($dirs->toArray()) == 0) {
                $dirContent = $disk->createDirectory($path);
                if ($dirContent) {
//                    echo 'Создана новая директория "' . $path . '"!';
                }
            }
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

                $old_folder =  (new Carbon($protokol->updated_dt))->formatLocalized('%Y-%m');
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

    /**
     * перенос старых протоколов в нулевой акт
     */
    public function moveProtokols()
    {
        $protokol = Protokol();
        $i=$e=0;
        try {
            foreach ($this->all() as $item) {
                $act_id = $item->act() - first()->id;
                $protokol
                    ->where('id', $item->id)
                    ->update(['act_id' => $act_id]);
                ++$i;
            }
        }
        catch (\Throwable $exception) {
            ++$e;
        }

        return "Update $i Skip $e\n";
    }


}
