<?php

namespace App\Admin\Controllers;

use App\Customer;
use App\Transaction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TrancactionControler extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Транзакции';
    protected $customer='';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $this->getHeader();

        $grid = new Grid(new Transaction());

        $grid->perPages([50, 100, 200, 500]);
        $grid->paginate(100);

        $grid->header(function ($query) {
            return "<div style='padding: 10px;'>Клиент: <b><a href=\"/admin/customers\" title='вернуться к списку клиентов'>".$this->customer."</a></b></div>";
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
//            $actions->disableEdit();
//            $actions->disableView();
        });

        $grid->model()->where('customer_id',session('customer_id'))->orderBy('created_at', 'desc');

        $grid->column('id', __('Id'));
        $grid->column('amount', __('Amount'));
        $grid->column('type', __('Type'));
        $grid->column('status', __('Status'));
        $grid->column('comment', __('Comment'));
        $grid->column('file', __('File'));
        $grid->column('response', __('Response'));
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
        $show = new Show(Transaction::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('amount', __('Amount'));
        $show->field('type', __('Type'));
        $show->field('status', __('Status'));
        $show->field('comment', __('Comment'));
        $show->field('file', __('File'));
        $show->field('response', __('Response'));
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
        $form = new Form(new Transaction());

        $form->hidden('customer_id')->value(session('customer_id'));
        $form->number('amount', __('Сумма расхода'))->required()->min(10);
        $form->hidden('type')-> value(1);
        $form->hidden('status')->value(2);
        $form->text('comment', __('Комментарий'));
        $form->file('file', __('Фото чека'));
//        $form->text('response', __('Ответ'));

        $form->saved(function (Form $form) {
            $customer = new Customer();
            $customer->calcLimit(session('customer_id'));
        });

        $form->deleted(function () {
            $customer = new Customer();
            $customer->calcLimit(session('customer_id'));
        });


        return $form;
    }



    private function getHeader()
    {
        $customers = Customer::find(session('customer_id'));
        $this->customer = $customers->name;
        $this->title .= ' - '.$customers->name;
    }




}
