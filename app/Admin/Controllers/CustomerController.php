<?php

namespace App\Admin\Controllers;

use App\Customer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CustomerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Клиенты компании';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Customer);
        $grid->filter(function($filter){

            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $filter->like('name', 'ФИО');
            $filter->like('code', 'Код клиента');
            $filter->equal('enabled')->radio([
                ''   => 'Все',
                0    => 'Не активны',
                1    => 'Активны',
            ]);

        });
//        $grid->column('id', __('ID'));
        $grid->column('code', __('Код'));
//        $grid->column('name', __('ФИО'));
        $grid->column('ФИО')->display(function () {
            return '<a href="/admin/protokols?set='.$this->id.'" title="Протоколы клиента '.$this->name.'">'.$this->name.'</a>';
        });
        $grid->column('enabled', __('Активен'));

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
        $show = new Show(Customer::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('code', __('Код'));
        $show->field('name', __('ФИО'));
        $show->field('enabled', __('Активен'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Customer);

        $form->text('code', __('Код'));
        $form->text('name', __('ФИО'));
        $form->switch('enabled', __('Активен'))->default(1);

        return $form;
    }
}
