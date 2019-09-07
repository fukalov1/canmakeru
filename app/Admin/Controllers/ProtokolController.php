<?php

namespace App\Admin\Controllers;

use App\Customer;
use App\Protokol;
use Encore\Admin\Controllers\AdminController;
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

        $grid->model()->where('customer_id',session('customer_id'));

        $grid->column('id', __('Id'));
        $grid->column('protokol_num', __('Protokol num'));
        $grid->column('pin', __('Pin'));
        $grid->column('protokol_photo', __('Protokol photo'));
        $grid->column('protokol_photo1', __('Protokol photo1'));
        $grid->column('meter_photo', __('Meter photo'));
//        $grid->column('customer_id', __('Customer id'));
        $grid->column('updated_dt', __('Updated dt'));
        $grid->column('lat', __('Lat'));
        $grid->column('lng', __('Lng'));
        $grid->column('protokol_dt', __('Protokol dt'));

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
        $show->field('updated_dt', __('Updated dt'));
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

        $form->number('protokol_num', __('Protokol num'));
        $form->number('pin', __('Pin'));
        $form->text('protokol_photo', __('Protokol photo'));
        $form->text('protokol_photo1', __('Protokol photo1'));
        $form->text('meter_photo', __('Meter photo'));
//        $form->number('customer_id', __('Customer id'));
        $form->datetime('updated_dt', __('Updated dt'))->default(date('Y-m-d H:i:s'));
        $form->decimal('lat', __('Lat'));
        $form->decimal('lng', __('Lng'));
        $form->datetime('protokol_dt', __('Protokol dt'))->default(date('Y-m-d H:i:s'));

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
