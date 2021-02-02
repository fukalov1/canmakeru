<?php

namespace App\Admin\Controllers;

use App\Customer;
use App\Exports\CustomerExport;
use App\Exports\CustomerExportXml;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Maatwebsite\Excel\Facades\Excel;

class CustomerReport1Controller extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Партнеры с поверками за период';

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
            $filter->between('protokols.protokol_dt', 'Период')->date();

        });

        $grid->disableActions();



        $grid->column('partner_code', __('Код партнера'));
        $grid->column('name', 'ФИО');
        $grid->protokols('Кол-во поверок')->display(function ($protokols) {
            if (!!request('protokols')) {
                $start = (request('protokols')['protokol_dt']['start']);
                $end = (request('protokols')['protokol_dt']['end']);
                $protokols = collect($protokols);
                $protokols = $protokols->filter(function ($item) use ($start,$end) {
                    return $item['protokol_dt']>=$start and $item['protokol_dt']<=$end ;
                });
            }
            return count($protokols);
        });
        $grid->act_count('Кол-во актов')->display(function ($id) {
            return Customer::find($this->id)->acts()->count();
        });
        $grid->act_good('Пригодных')->display(function () {
            return  Customer::find($this->id)->acts()->where('type', 'пригодны')->count();
        });
        $grid->act_bad('Непригодных')->display(function () {
            return Customer::find($this->id)->acts()->where('type', 'непригодны')->count();
        });
        $grid->act_brak('Испорченных')->display(function () {
            return Customer::find($this->id)->acts()->where('type', 'испорчен')->count();
        });


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
        $show->field('code', __('Code'));
        $show->field('name', __('Name'));
        $show->field('enabled', __('Enabled'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('deleted_at', __('Deleted at'));

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

        $form->text('code', __('Code'));
        $form->text('name', __('Name'));
        $form->number('enabled', __('Enabled'))->default(1);

        return $form;
    }

    public function exportCsvFgis()
    {
        return new CustomerExport();
    }

}
