<?php

namespace App\Admin\Controllers;

use App\Customer;
use App\Exports\CustomerExport;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Maatwebsite\Excel\Facades\Excel;

class CustomerReportController extends AdminController
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



        $grid->column('code', __('Код'));
        $grid->column('ФИО')->display(function () {
            return '<a href="/admin/protokols?set='.$this->id.'" title="Поверки клиента '.$this->name.'">'.$this->name.'</a>';
        });
        $grid->protokols('Поверок')->display(function ($protokols) {
            if (!!request('protokols')) {
                $start = (request('protokols')['protokol_dt']['start']);
                $end = (request('protokols')['protokol_dt']['end']);
//                dd($start, $end);
                $protokols = collect($protokols);
                $protokols = $protokols->filter(function ($item) use ($start,$end) {
                    return $item['protokol_dt']>=$start and $item['protokol_dt']<=$end ;
                });
//                dd(count($protokols));
//                    ->where('protokol_dt', '>=', $start)
//                    ->where('protokol_dt', '<=', $end)->get();
            }
            return count($protokols);
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

    public function exportXmlFGIS()
    {
        return new CustomerExport();
    }

    public function exportCsvFgis()
    {
        return new CustomerExport();
    }

}
