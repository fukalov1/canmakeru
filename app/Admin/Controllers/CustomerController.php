<?php

namespace App\Admin\Controllers;

use App\Customer;
use App\SlaveCustomer;
use DemeterChain\C;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Actions\Post\Slave;


class CustomerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Партнеры';

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
            $filter->like('comment', 'Комментарий');
            $filter->like('code', 'Код клиента');
            $filter->like('email', 'E-mail');
            $filter->like('protokols.protokol_num', 'Номер свидетельства');
            $filter->equal('enabled')->radio([
                ''   => 'Все',
                0    => 'Не активны',
                1    => 'Активны',
            ]);

        });

        if (Admin::user()->roles[0]->slug!='administrator') {
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableView();
            });
        }
        else {
            $grid->actions(function ($actions) {
                $actions->add(new Slave());
            });
        }

        $grid->column('code', __('Код'));
        $grid->name('ФИО!
        ')->display(function () {
            return '<a href="/admin/protokols?set='.$this->id.'" title="Поверки клиента '.$this->name.'">'.$this->name.'</a>';
        })->sortable();
        $grid->dinamic('Динамика поверок')->display(function () {
            return '<a href="/admin/customer_chart?set='.$this->id.'" title="Динамика поверок "><span class="fa fa-bar-chart"/></a>';
        });
        $grid->column('comment', __('Комментарий'));
        $grid->column('enabled', __('Активен'));
        $grid->column('email', __('E-mail'));

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
        $show->field('comment', __('Комментарий'));
        $show->field('enabled', __('Активен'));
        $show->field('email', __('E-mail'));

        $show->slave_customers('Работники', function ($slave_customers) {

            $slave_customers->resource('/admin/slave_customers');

            $slave_customers->id();
            $slave_customers->slave_id('ФИО')->display(function ($slave_id) {
                return Customer::find($slave_id)->name;
            });

            $slave_customers->filter(function ($filter) {
                $filter->like('slave_id');
            });
        });

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

        $form->text('code', __('Код'))->required(true);;
        $form->text('name', __('ФИО'));
        $form->text('comment', __('Комментарий'));
        $form->switch('enabled', __('Активен'))->default(1);
        $form->email('email', __('E-mail'))->required(true);

//        $form->hasMany('slave_customers', 'Работники', function (Form\NestedForm $form) {
//            $form->select('slave_id', 'ФИО')->options(function ($id) {
//                $customers = Customer::select('id','name')->get()->sortBy('name');
//                return $customers->pluck('name', 'id');
//            });
//        });

        return $form;
    }
}
