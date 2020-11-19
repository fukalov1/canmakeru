<?php

namespace App\Admin\Controllers;

use App\Customer;
use App\Transaction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CheckController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Чеки';
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

        $grid->header(function ($query) {
            return "<div style='padding: 10px;'>Клиент: <b><a href=\"/admin/customers\" title='вернуться к списку клиентов'>".$this->customer."</a></b></div>";
        });

        $grid->disableCreateButton();
        $grid->disableRowSelector();


        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->model()
            ->where('customer_id',session('customer_id'))
            ->where('type', 2)
            ->orderBy('created_at', 'desc');

        $grid->column('id', __('Id'));
        $grid->column('RequestId', __('RequestId'));
        $grid->column('amount', __('Сумма'));
        $grid->column('count', __('Кол-во'));
        $grid->column('status', __('Статус'));

        $grid->column('CheckQueueId', __('CheckQueueId'))->display(function () {
            $link = $this->link ? '<a href="'.$this->link.'" target="_blank">'.$this->CheckQueueId.'</a>' : $this->CheckQueueId;
            return $link;
        });
        $grid->column('created_at', __('Создано'));

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
        $show->field('uuid', __('Uuid'));
        $show->field('type', __('Type'));
        $show->field('status', __('Status'));
        $show->field('count', __('Count'));
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

        $form->number('customer_id', __('Customer id'));
        $form->decimal('amount', __('Amount'));
        $form->text('uuid', __('Uuid'));
        $form->text('type', __('Type'));
        $form->text('status', __('Status'));
        $form->number('count', __('Count'))->default(1);
        $form->text('comment', __('Comment'));
        $form->file('file', __('File'));
        $form->text('response', __('Response'));

        return $form;
    }

    private function getHeader()
    {
        $customers = Customer::find(session('customer_id'));
        $this->customer = $customers->name;
        $this->title .= ' - '.$customers->name;
    }

}
