<?php

use App\Protokol;
use App\Act;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Yandex\Disk\DiskClient;

class UpdateMeterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $protokols = Protokol::whereRaw('year(protokol_dt)', 2021)
            ->whereRaw("protokol_photo = ''")
            ->skip(16)
            ->take(1)
            ->get();

       foreach ($protokols as $protokol) {
           try {
               preg_match('/meter\_(.*)\-(.*)\.jpg/',$protokol->meter_photo, $matches);
               $uid = uniqid();
               $path = '/'.date('Y', strtotime($protokol->protokol_dt)).'-'.date('m', strtotime($protokol->protokol_dt)).'/';
               $result = $this->moveYandexDiskFile($path.$protokol->meter_photo, $path."meter_$uid-".$matches[2].".jpg");

               if ($result['success']) {
                   Act::find($protokol->act_id)->update(['name' => $uid]);
                   Protokol::find($protokol->id)->update(['meter_photo' => "meter_$uid.jpg"]);
               }
               echo "$path : {$protokol->act->number_act} - {$protokol->meter_photo}$ - meter_$uid.jpg - {$result['success']} - {$result['message']}\n";

           }
           catch (\Throwable $exception) {

           }
       }

    }


    private function moveYandexDiskFile($old_file, $new_file)
    {

        $error = '';
        $success = false;

        try {

            $diskClient = new DiskClient('AgAAAAA3ol63AAXdp2N7x58wMEIMnzayK025AEE');
            $diskClient->setServiceScheme(DiskClient::HTTPS_SCHEME);

            $diskSpace = $diskClient->diskSpaceInfo();

//            dd($diskSpace['availableBytes'], $old_file, $new_file);


            if ($diskClient->move($old_file, $new_file)) {
                echo 'Файл "' . $old_file . '" перемещен в "' . $new_file. '"';
                $success = true;
            }
        }
        catch (Exception $ex) {
            $error = 'Ошибка переименования файла '.$old_file.' -> '.$new_file." Error: {$ex->getMessage()}";
            Log::info('Ошибка переименования файла '.$old_file.' -> '.$new_file);
        }

        return ['success' => $success, 'message' => $error];

    }
}
