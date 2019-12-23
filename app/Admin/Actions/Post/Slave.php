<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Slave extends RowAction
{
    public $name = 'Работники';

    public function href()
    {

        // $model ...
        return "/admin/slave_customers?set=".$this->getKey();

//        return $this->response()->success('Success message.')->refresh();
    }

}
