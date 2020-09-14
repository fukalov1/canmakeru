<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\RowAction;
use App\Admin\Actions\MyRowAction;

use Illuminate\Database\Eloquent\Model;

class ExportOneFgis extends MyRowAction
{
    public $name = 'Экспорт ФГИС';

    public function href()
    {
        return "/admin/export-one-fgis/".$this->getKey();
    }

}
