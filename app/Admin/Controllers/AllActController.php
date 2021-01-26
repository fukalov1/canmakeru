<?php

namespace App\Admin\Controllers;

use App\Act;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AllActController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $customer='';
    protected $title = 'Акты';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Act());

        $grid->perPages([50, 100, 200, 500]);
        $grid->paginate(100);

        $grid->header(function ($query) {
            return "<div style='padding: 10px;'><b><a href=\"/admin/customers\" title='вернуться к списку клиентов'>Клиенты</a></b></div>";
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
            $filter->like('number_act', 'Номер акта');
            $filter->like('protokols.protokol_num', 'Номер свидетельства');
            $filter->like('protokols.serialNumber', 'Заводской номер');
            $filter->equal('type')->radio([
                ''   => 'Все',
                'Пригодны' => 'Пригодны',
                'Непригодны' => 'Непригодны',
                'Испорчен' => 'Испорчен',
            ]);

        });

        $grid->model()->orderBy('created_at', 'desc');

        $grid->column('customer.name', __('Поверитель'))->sortable();

        $grid->column('number_act', __('Номер акта'))->display(function () {
            $name = $this->name ? "({$this->name})" : '';
            return '<a href="/admin/protokols?set='.$this->id.'" title="Акты с поверками клиента '.$this->number_act.'">'.$this->number_act.'</a>';
        })->sortable();

        $grid->column('pin', __('ПИН'));

        $grid->column('miowner', __('Владелец'));

        $grid->column('date', __('Дата'));


        $grid->column('address', __('Примечание'));

        $grid->photos('Фото')->modal('Фото акта', function ($model) {
            $str = '';
            $file = preg_replace('/photos\//','',$this->meter_photo);
            $matches = [];
            preg_match('/(\d\d\d\d)\-(\d\d)/', $this->date,$matches);
            if (count($matches) > 0) {
                $str .= '<div class="row"><div class="col-lg-4"><label>Акт</label><a target="_blank" href="/photo/'.$matches[1].'/'.$matches[2].'/act_' . $this->name . '.jpg"><img src="/preview/'.$matches[1].'/'.$matches[2].'/act_' . $this->name . '.jpg"></a></div></div>';
            }
            return $str;
        });

        $grid->column('type', __('Тип'));
        $grid->column('lat', __('Шир.'));
        $grid->column('lng', __('Дол.'));


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
        $show = new Show(Act::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('name', __('Name'));
        $show->field('number_act', __('Number act'));
        $show->field('pin', __('Pin'));
        $show->field('lat', __('Lat'));
        $show->field('lng', __('Lng'));
        $show->field('address', __('Address'));
        $show->field('date', __('Date'));
        $show->field('type', __('Type'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('miowner', __('Miowner'));
        $show->field('temperature', __('Temperature'));
        $show->field('hymidity', __('Hymidity'));
        $show->field('cold_water', __('Cold water'));
        $show->field('hot_water', __('Hot water'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Act());

        $form->number('customer_id', __('Customer id'));
        $form->text('name', __('Name'));
        $form->text('number_act', __('Number act'));
        $form->text('pin', __('Pin'));
        $form->decimal('lat', __('Lat'));
        $form->decimal('lng', __('Lng'));
        $form->text('address', __('Address'));
        $form->datetime('date', __('Date'))->default(date('Y-m-d H:i:s'));
        $form->text('type', __('Type'));
        $form->text('miowner', __('Miowner'))->default('физ. лицо');
        $form->text('temperature', __('Temperature'))->default('24,2');
        $form->text('hymidity', __('Hymidity'))->default('36');
        $form->text('cold_water', __('Cold water'))->default('7,9');
        $form->text('hot_water', __('Hot water'))->default('62,7');

        return $form;
    }
}
