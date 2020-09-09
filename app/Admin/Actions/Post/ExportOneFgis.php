<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class ExportOneFgis extends RowAction
{
    public $name = 'Экспорт ФГИС';

    public function href()
    {
        return "/admin/export-one-fgis/".$this->getKey();
    }

}
