<?php

namespace App\Admin\Controllers;

use App\Customer;
use App\Lib\KitOnline\KitOnlineService;
use App\Transaction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Log;

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

        $grid->model()
            ->where('customer_id',session('customer_id'))
            ->where('type', 1)
            ->orderBy('created_at', 'desc');

        $grid->column('id', __('Id'));
        $grid->column('uuid', __('UUID'));
        $grid->column('amount', __('Сумма'));
//        $grid->column('type', __('Type'));
        $grid->column('status', __('Статус'));
        $grid->column('comment', __('Комментарий'));
        $grid->column('file', __('Фото чека'));
//        $grid->column('response', __('Response'));
        $grid->column('created_at', __('Создан'));
//        $grid->column('updated_at', __('Updated at'));

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
        $form->hidden('uuid')->value($this->GUID());
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

    private function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }


    private function getHeader()
    {
        $customers = Customer::find(session('customer_id'));
        $this->customer = $customers->name;
        $this->title .= ' - '.$customers->name;
    }


    public function updateStatus()
    {

        $api = new KitOnlineService;

        try {
            $transactions = Transaction::where('status',1)
                ->where('type', 'приход')
                ->whereNotNull('CheckQueueId')
                ->get();
            foreach ($transactions as $transaction) {
                $result = $api->stateCheck($transaction);
                if ($result->ResultCode===0) {
                    $this->updateTransaction($transaction->id, $result);

                }
                else if (array_key_exists ('response' , $result)) {
                    Log::info('KitOnline Rest Api error Method: stateCheck. Result: '. $result['message']);
                }
            }
        }
        catch (\Throwable $exception) {
            Log::info('KitOnline Rest Api error Method: stateCheck. Result: '. $exception->getMessage());
        }
    }


    private function updateTransaction($id, $result)
    {
        $transaction = Transaction::find($id);
        $transaction->response = json_encode( (array)$result );;

        if ($result->CheckState->State == 1000) {
            $transaction->status = 2;
        }
        else if ($result->CheckState->State == 1010) {
            $transaction->status = 3;
        }

        $transaction->save();
    }

}
