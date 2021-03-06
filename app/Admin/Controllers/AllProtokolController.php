<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Protokol\BatchClearExport;
use App\Protokol;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AllProtokolController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Protokol';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Protokol());

        $grid->perPages([50, 100, 200, 500]);
        $grid->paginate(100);

        $grid->header(function ($query) {
            return "<div style='padding: 10px;'><b><a href=\"/admin/customers\" title='вернуться к списку клиентов'>Клиенты</a> </div>";
        });
        $grid->filter(function($filter){

            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $filter->like('customer.name', 'ФИО');
            $filter->like('address', 'Комментарий');
            $filter->like('customer.code', 'Код клиента');
            $filter->like('customer.partner_code', 'Код партнера');
            $filter->like('customer.email', 'E-mail');
            $filter->like('act.number_act', 'Номер акта');
            $filter->like('protokol_num', 'Номер свидетельства');
            $filter->like('serialNumber', 'Заводской номер');
            $filter->equal('act.type')->radio([
                ''   => 'Все',
                'Пригодны' => 'Пригодны',
                'Непригодны' => 'Непригодны',
                'Испорчен' => 'Испорчен',
            ]);

        });

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
        $grid->model()->orderBy('protokol_dt', 'desc');

        $grid->column('customer.name', __('Поверитель'))->sortable();
        $grid->column('protokol_num', __('№ свид-ва'))->sortable();
        $grid->column('protokol_dt', __('Дата свид-ва'))->sortable();
        $grid->column('pin', __('Pin'));
        $grid->photos('Фото')->modal('Фото поверки', function ($model) {
            $str = '';
            $matches = [];
            preg_match('/(\d\d\d\d)\-(\d\d)/', $this->protokol_dt,$matches);
            if (count($matches) > 0) {
                $file = preg_replace('/photos\//','',$this->meter_photo);
                $str .= '<div class="row"><div class="col-12"><label>Счетчик</label><a target="_blank" href="/photo/'.$matches[1].'/'.$matches[2].'/'.$file.'"><img src="/preview/'.$matches[1].'/'.$matches[2].'/'.$file.'"></a></div></div>';
            }
            return $str;
        });
//        $grid->column('lat', __('Шир.'));
//        $grid->column('lng', __('Дол.'));
        $grid->column('siType', 'Тип СИ');
        $grid->column('waterType', 'Тип воды');
        $grid->column('regNumber', 'Регистр. №');
        $grid->column('serialNumber', 'Заводской №');
        $grid->column('checkMethod', 'Методика поверки');
        $grid->column('nextTest', 'След. поверка');
//        $grid->column('updated_dt', __('Дата изменения'));
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
        $show->field('act_id', __('Act id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('protokol_num', __('Protokol num'));
        $show->field('pin', __('Pin'));
        $show->field('protokol_photo', __('Protokol photo'));
        $show->field('protokol_photo1', __('Protokol photo1'));
        $show->field('meter_photo', __('Meter photo'));
        $show->field('lat', __('Lat'));
        $show->field('lng', __('Lng'));
        $show->field('protokol_dt', __('Protokol dt'));
        $show->field('updated_dt', __('Updated dt'));
        $show->field('siType', __('SiType'));
        $show->field('waterType', __('WaterType'));
        $show->field('regNumber', __('RegNumber'));
        $show->field('serialNumber', __('SerialNumber'));
        $show->field('checkInterval', __('CheckInterval'));
        $show->field('checkMethod', __('CheckMethod'));
        $show->field('exported', __('Exported'));
        $show->field('nextTest', __('NextTest'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Protokol());

        $form->number('act_id', __('Act id'));
        $form->number('customer_id', __('Customer id'));
        $form->text('protokol_num', __('Protokol num'));
        $form->number('pin', __('Pin'));
        $form->text('protokol_photo', __('Protokol photo'));
        $form->text('protokol_photo1', __('Protokol photo1'));
        $form->text('meter_photo', __('Meter photo'));
        $form->decimal('lat', __('Lat'));
        $form->decimal('lng', __('Lng'));
        $form->datetime('protokol_dt', __('Protokol dt'))->default(date('Y-m-d H:i:s'));
        $form->text('siType', __('SiType'));
        $form->text('waterType', __('WaterType'));
        $form->text('regNumber', __('RegNumber'));
        $form->text('serialNumber', __('SerialNumber'));
        $form->text('checkInterval', __('CheckInterval'));
        $form->text('checkMethod', __('CheckMethod'));
        $form->number('exported', __('Exported'));
        $form->datetime('nextTest', __('NextTest'))->default(date('Y-m-d H:i:s'));
        $form->datetime('updated_at', __('Updated at'))->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
