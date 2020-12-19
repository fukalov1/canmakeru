<?php

namespace App\Admin\Controllers;

use App\Act;
use App\Customer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ActController extends AdminController
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
        $this->getHeader();

        $grid = new Grid(new Act());

        $grid->perPages([50, 100, 200, 500]);
        $grid->paginate(100);

        $grid->header(function ($query) {
            return "<div style='padding: 10px;'>Клиент: <b><a href=\"/admin/customers\" title='вернуться к списку клиентов'>".$this->customer."</a></b></div>";
        });

        $grid->model()->where('customer_id',session('customer_id'))->orderBy('created_at', 'desc');

//        $grid->column('number_act', __('Номер'));
        $grid->column('number_act', __('Номер акта'))->display(function () {
            $name = $this->name ? "({$this->name})" : '';
            return '<a href="/admin/protokols?set='.$this->id.'" title="Акты с поверками клиента '.$this->number_act.'">'.$this->number_act.' '.$name.'</a>';
        })->sortable();

//        $grid->column('customer_id', __('Customer id'));
//        $grid->column('name', __('Наименование'));
        $grid->column('type', __('Тип'));
        $grid->column('lat', __('Шир.'));
        $grid->column('lng', __('Дол.'));
//        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Дата'));

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
        $show->field('type', __('Type'));
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
        $form = new Form(new Act());

        $form->hidden('customer_id')->value(session('customer_id'));
        $form->text('number_act', __('Номер'));
        $form->text('name', __('Наименование'));
        $form->text('type', __('Тип'));
        $form->decimal('lat', __('Ширина'))->default(0);
        $form->decimal('lng', __('Долгота'))->default(0);
        $form->text('address', 'Адрес');

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
