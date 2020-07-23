<?php

namespace App\Exports;

use App\Customer;
use App\Protokol;
use Maatwebsite\Excel\Excel;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;

class CustomerExport implements FromCollection, Responsable
{
    use Exportable;

    private $fileName = 'protokols.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $protokols = collect([]);
        $columns = ['TypePOV','GosNumberPOV','NamePOV','DesignationSiPOV','DeviceMarkPOV','DeviceCountPOV','SerialNumPOV','SerialNumEndPOV','CalibrationDatePOV','NextcheckDatePOV','MarkCipherPOV','DocPOV','DeprcatedPOV','NumCertfPOV','NumSvidPOV','PrimPOV','ScopePOV','StandartPOV','GpsPOV','SiPOV','SoPOV'];
        $protokols->push($columns);

        $customers = Customer::where('export_fgis',1)->get();
        foreach ($customers as $customer) {
            foreach ($customer->new_protokols as $protokol) {
                if ($protokol->regNumber) {
                    $protokols->push([
                        '1',
                        $protokol->regNumber, '', '', '', 1,
                        $protokol->serialNumber,
                        '',
                        date('Y-m-d', strtotime($protokol->updated_dt)),
                        date('Y-m-d', strtotime($protokol->nextTest)),
                        'Нет данных',  $protokol->checkMethod, 'Пригодно',
                        $this->getProtokolNumber($protokol->protokol_num),
                        '', '', '', '', 'гэт63-2017', '', ''
                    ]);
                }
            }
            Protokol::where('customer_id', $customer->id)
                ->update(['exported' => 1]);
        }
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
