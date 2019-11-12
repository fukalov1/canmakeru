<?php

namespace App\Admin\Controllers;

use App\Customer;
use App\SlaveCustomer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SlaveCustomerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\SlaveCustomer';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SlaveCustomer);

        $grid->column('id', __('Id'));
        $grid->column('customer_id', __('Customer id'));
        $grid->column('slave_id', __('Slave id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(SlaveCustomer::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('slave_id', __('Slave id'));
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
        $form = new Form(new SlaveCustomer);

        $customer_id = 0;
        if (request()->customer_id)
            $customer_id = request()->customer_id;

        $form->hidden('customer_id')->value($customer_id);
//        $form->number('slave_id', __('Slave id'));
        $form->select('slave_id', 'ФИО')->options(function ($id) {
            $customers = Customer::select('id','name')->get()->sortBy('name');
            return $customers->pluck('name', 'id');
        });

        return $form;
    }
}
