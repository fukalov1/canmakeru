<?php

namespace App\Admin\Extensions;

use App\Customer;
use Encore\Admin\Grid\Exporters\ExcelExporter;

class ExcelExpoter extends ExcelExporter
{
    public function export()
    {

        $params = request()->input('protokols');
        $start = $params['protokol_dt']['start'];
        $end = $params['protokol_dt']['end'];

        $filename = time().'.csv';
        try {
            if ($fh = fopen(storage_path('admin') . $filename, "w+")) {
                $data = $this->getCollection();
                $customers = Customer::whereIn('id', $data->pluck('id')->all())->with('acts')->with('protokols')->get();
//                dd($this->grid->getFilter()->getCurrentScope());
                foreach ($customers as $item) {
                    $acts = $item->acts()
                        ->whereBetween('acts.date', [$start." 00:00:00", $end." 23:59:59"])
                        ->get();
//                    dd($acts->toArray(),$item->acts->count());
                    $protokols = $item->protokols()->count();
                    $good = $item->acts()
                        ->whereBetween('acts.date', [$start." 00:00:00", $end." 23:59:59"])
                        ->where('type', 'пригодны')
                        ->get()->count();
                    $bad = $acts->where('type', 'непригодны')->count();
                    $brak = $acts->where('type', 'испорчен')->count();

                    fwrite($fh, "{$item['partner_code']};{$item['name']};$protokols;{$acts->count()};$good;$bad;$brak;\n");
                }

                // This logic get the columns that need to be exported from the table data
//                $rows = collect($this->getData())->map(function ($item) {
//                    return $item;
//                });
                fclose($fh);
            }
        }
        catch (\Throwable $exception) {
            dd($exception->getMessage());
        }


    }
}
