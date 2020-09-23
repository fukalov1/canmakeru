<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Protokol\BatchClearExport;
use App\Customer;
use App\Protokol;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProtokolController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */

    protected $customer='';
    protected $title = 'Протоколы';


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $this->getHeader();


        $grid = new Grid(new Protokol);



        $grid->header(function ($query) {
            return "<div style='padding: 10px;'>Клиент: <b><a href=\"/admin/customers\" title='вернуться к списку клиентов'>".$this->customer."</a></b></div>";
        });

//        $grid->tools(function ($tools) {
//            $tools->batch(function ($batch) {
//                $batch->add(new BatchClearExport());
//            });
//        });

        $grid->batchActions(function ($batch) {
            $batch->add(new BatchClearExport());
        });

        $grid->filter(function($filter){

            // Remove the default id filter
            $filter->disableIdFilter();

            $filter->like('protokol_num', 'Номер свидетельства');
            $filter->between('protokol_dt', 'Дата свидетельства')->date();
            $filter->like('exported', 'Пакет выгрузки')->default(0);
        });

        if (Admin::user()->roles[0]->slug!='administrator') {
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableView();
            });
        }

        $grid->model()->where('customer_id',session('customer_id'))->orderBy('protokol_dt', 'desc');

        $grid->column('protokol_num', __('№ свид-ва'))->sortable();
        $grid->column('protokol_dt', __('Дата свид-ва'))->sortable();
        $grid->column('pin', __('Pin'));
        $grid->photos('Фото')->modal('Фото поверки', function ($model) {
            $matches = [];
            preg_match('/(\d\d\d\d)\-(\d\d)/', $this->protokol_dt,$matches);
            $file = preg_replace('/photos\//','',$this->protokol_photo);
            $str = '<div class="row"><div class="col-lg-4"><label>Свидетельство</label><a target="_blank" href="/photo/'.$matches[1].'/'.$matches[2].'/'.$file.'"><img src="/preview/'.$matches[1].'/'.$matches[2].'/'.$file.'"></a></div>';
            $file = preg_replace('/photos\//','',$this->protokol_photo1);
            $str .= '<div class="col-lg-4"><label>Свидетельство (обратная сторона)</label><a target="_blank" href="/photo/'.$matches[1].'/'.$matches[2].'/'.$file.'"><img src="/preview/'.$matches[1].'/'.$matches[2].'/'.$file.'"></a></div>';
            $file = preg_replace('/photos\//','',$this->meter_photo);
            $str .= '<div class="col-lg-4"><label>Счетчик</label><a target="_blank" href="/photo/'.$matches[1].'/'.$matches[2].'/'.$file.'"><img src="/preview/'.$matches[1].'/'.$matches[2].'/'.$file.'"></a></div></div>';
            return $str;
        });
        $grid->column('lat', __('Шир.'));
        $grid->column('lng', __('Дол.'));
        $grid->column('siType', 'Тип СИ');
        $grid->column('waterType', 'Тип воды');
        $grid->column('regNumber', 'Регистр. №');
        $grid->column('serialNumber', 'Заводской №');
        $grid->column('checkMethod', 'Методика поверки');
        $grid->column('nextTest', 'След. поверка');
//        $grid->column('updated_at', __('Дата изменения'));
        $grid->column('exported', __('Пакет'))->editable();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Protokol::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('protokol_num', __('Protokol num'));
        $show->field('pin', __('Pin'));
        $show->field('protokol_photo', __('Protokol photo'));
        $show->field('protokol_photo1', __('Protokol photo1'));
        $show->field('meter_photo', __('Meter photo'));
//        $show->field('customer_id', __('Customer id'));
        $show->field('updated_at', __('Updated dt'));
        $show->field('lat', __('Lat'));
        $show->field('lng', __('Lng'));
        $show->field('protokol_dt', __('Protokol dt'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Protokol);

        $form->number('exported', __('Пакет выгрузки'))->default(0);
        $form->number('protokol_num', __('Protokol num'));
        $form->number('pin', __('Pin'));
        $form->text('protokol_photo', __('Protokol photo'));
        $form->text('protokol_photo1', __('Protokol photo1'));
        $form->text('meter_photo', __('Meter photo'));
//        $form->number('customer_id', __('Customer id'));
//        $form->datetime('updated_at', __('Updated dt'))->default(date('Y-m-d H:i:s'));
        $form->decimal('lat', __('Lat'));
        $form->decimal('lng', __('Lng'));
        $form->datetime('protokol_dt', __('Protokol dt'))->default(date('Y-m-d H:i:s'));
        $form->text('siType', 'Тип СИ');
        $form->text('waterType', 'Тип воды');
        $form->text('regNumber', 'Регистрационный номер');
        $form->text('serialNumber', 'Заводской номер');
        $form->number('checkInterval', 'Интервал поверки');
        $form->text('checkMethod', 'Методика поверки');

        return $form;
    }

    public function getHeader()
    {
        $customers = Customer::find(session('customer_id'));
//        dd($customers->name);
            $this->customer = $customers->name;
            $this->title .= ' - '.$customers->name;
//            dd($this->title);
    }

}
