<?php

namespace App\Exports;

use App\Customer;
use App\Protokol;
use Illuminate\Contracts\Support\Responsable;

class CustomerExportXml
{
    private $fileName = 'protokols.xml';

    private $headers = [
        'Content-Type' => 'text/xml',
    ];

    /**
    * @return \Illuminate\Support\Collection
    */
    public function getExport()
    {
        $protokols = '';

        $customers = Customer::where('export_fgis',1)->get();
        foreach ($customers as $customer) {
            foreach ($customer->new_protokols as $protokol) {
                if ($protokol->regNumber) {

                    $protokols .= '<gost:result';

                    $protokols .= '<gost:miInfo>
                        <gost:singleMI>
                            <gost:mitypeNumber>' . $protokol->ciType . '</gost:mitypeNumber>
                            <gost:manufactureNum>' . $protokol->regNumber . '</gost:manufactureNum>
                            <gost:modification>' . $protokol->serialNumber . '</gost:modification>
                        </gost:singleMI>
                    </gost:miInfo>';

                    $protokols .= '<gost:signCipher>' . config('signCipher', 'ГСЧ') . '</gost:signCipher>
                        <gost:vrfDate>' . $protokols->protokol_dt . '</gost:vrfDate>
                        <gost:validDate>' . $protokols->nextTest . '</gost:validDate>
                        <gost:applicable>
                            <gost:certNum>' . $this->getProtokolNumber($protokol->protokol_num) . '</gost:certNum>
                            <gost:signPass>false</gost:signPass>
                            <gost:signMi>false</gost:signMi>
                        </gost:applicable>
                        <gost:docTitle>' . $protokol->checkMethod . '</gost:docTitle>';

                    $protokols .= '<gost:means>
                        <gost:uve>
                           <gost:number>Эталон брать из пользователя (по умолчанию 3.2.ВЮМ.0023.2019)</gost:number>
                        </gost:uve>
                        <gost:mieta>
                            <gost:number> Появляется вместо уве если СИ кк эталон</gost:number>
                        </gost:mieta>
                        <gost:mis>
                        <gost:mi>
                            <gost:typeNum></gost:typeNum>
                            <gost:manufactureNum></gost:manufactureNum>
                        </gost:mi>
                    </gost:mis>
        </gost:means>';


                    $protokols .= '</gost:result>';
                }
            }
            Protokol::where('customer_id', $customer->id)
                ->update(['exported' => 1]);
        }


        $protokols = '<gost:result>'.$protokols.'</gost:result>';

        return $protokols;
    }

    private function getProtokolNumber($protokol_num)
    {
        if ($protokol_num) {
            return intval(substr($protokol_num, 0, -7)) . '-' . intval(substr($protokol_num, -7, 2)) . '-' . intval(substr($protokol_num, -5));
        }
        else {
            return '';
        }
    }


}
