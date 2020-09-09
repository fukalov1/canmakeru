<?php

namespace App\Admin\Actions\Protokol;

use App\Protokol;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchClearExport extends BatchAction
{
    public $name = 'очистить экспорт';

    public function handle(Collection $collection)
    {
        $protokol = new Protokol();
        foreach ($collection as $model) {
            $protokol->find($model->id)->update(['exported' => 0]);
        }

        return $this->response()->success('Success message...')->refresh();
    }

}
