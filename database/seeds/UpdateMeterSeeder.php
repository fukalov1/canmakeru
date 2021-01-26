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

        $acts = Act::where('name', null)->take(1)->get();

        foreach ($acts as $act) {
            $uid = uniqid();
            $path = '/'.date('Y', strtotime($act->date)).'-'.date('m', strtotime($act->date)).'/';
            $result = $this->moveYandexDiskFile($path.'act_'.$act->number_act.'.jpg', $path."act_$uid.jpg");
            if ($result['success']) {
                Act::find($act->id)->update(['name' => $uid]);
            }
            echo "$path : {$act->number_act} - {$result['success']} - {$result['message']}\n";

            foreach ($act->meters() as $protokol) {
                try {
                    preg_match('/meter\_(.*)\-(.*)\.jpg/',$protokol->meter_photo, $matches);


                    $nmbr = $matches[2];
                    $result = $this->moveYandexDiskFile($path.$protokol->meter_photo, $path."meter_$uid-".$nmbr.".jpg");

                    if ($result['success']) {
                        Protokol::find($protokol->id)->update(['meter_photo' => "meter_$uid-$nmbr.jpg"]);
                    }
                    echo "$path : {$protokol->act->number_act} - {$result['success']} - {$result['message']}\n";

                }
                catch (\Throwable $exception) {

                }
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
                $error =  'Файл "' . $old_file . '" перемещен в "' . $new_file. '"';
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
