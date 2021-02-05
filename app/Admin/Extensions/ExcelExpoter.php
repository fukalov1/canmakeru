<?php

namespace App\Admin\Extensions;

use Encore\Admin\Grid\Exporters\AbstractExporter;

class ExcelExpoter extends AbstractExporter
{
    public function export()
    {
        $filename = time().'.csv';
        try {
            if ($fh = fopen(storage_path('admin') . $filename, "w+")) {
                $data = $this->getCollection();
                dd($this->grid->getFilter());
                foreach ($data as $item) {
                    dd($item);
                    fwrite($fh, "{$item['partner_code']};{$item['name']};".count($item['protokols']).";{$item['act_count']};\n");
                }
                fclose($fh);
            }
        }
        catch (\Throwable $exception) {
            dd($exception->getMessage());
        }
        return response()->download(fopen(storage_path('admin').$filename));

    }
}
