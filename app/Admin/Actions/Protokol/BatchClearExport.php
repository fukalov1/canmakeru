<?php

namespace App\Admin\Actions\Protokol;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchClearExport extends BatchAction
{
    public $name = 'очистить экспорт';

    public function handle(Collection $collection)
    {
        foreach ($collection as $model) {
            // ...
        }

        return $this->response()->success('Success message...')->refresh();
    }

}