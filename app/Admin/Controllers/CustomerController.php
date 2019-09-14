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
            return '<a href="/admin/protokols?set='.$this->id.'" title="Поверки клиента '.$this->name.'">'.$this->name.'</a>';
        });
        $grid->dinamic('Динамика поверок')->display(function () {
            return '<a href="/admin/customer_chart?set='.$this->id.'" title="Динамика поверок "><span class="fa fa-bar-chart"/></a>';
        });
//        $grid->dinamic('Динамика поверок')->modal('Динамика поверок счетчиков', function ($model) {
//            $str='';
//            $id = $model->id;
//            $quest  = Customer::join('protokols','customers.id','protokols.customer_id')
//                ->select(\DB::raw('date_format(protokols.protokol_dt, "%Y-%m") as date, count(protokols.protokol_num) count'))
//                ->whereRaw('date_format(protokol_dt, "%Y-%m") <> "0000-00"')
//                ->where('customer_id', $id)
//                ->groupBy(\DB::raw('date_format(protokol_dt, "%Y-%m")'))
//                ->get()->toArray();

//            $str = "    <div class=\"row\">
//        <div class=\"col-lg-12\">
//            <canvas id=\"myChart1\" width=\"800\" height=\"400\"></canvas>
//            <script>
//                $(function () {
//                    var ctx = document.getElementById(\"myChart1\").getContext('2d');
//                    var myChart1 = new Chart(ctx, {
//                        type: 'bar',
//                        data: {
//                            labels: [";
//
//                                foreach($quest as $item) {
//                                    $str .= "'" . $item['date'] . "',";
//                                }
//                            $str .= "],
//                            datasets: [{
//                                label: 'Динамика поверки счетчиков',
//                                data: [";
//
//                                    foreach($quest as $item) {
//                                        $str .= $item['count']. ",";
//                                    }
//                                $str .= "],
//                                backgroundColor: [";
//                                    foreach($quest as $item) {
//                                        $str .= "'rgba(54, 162, 235, 0.2)',";
//                                    }
//                                $str .= "],
//                                borderColor: [";
//                                    foreach($quest as $item) {
//                                        $str .= "'rgba(255,99,132,1)',";
//                                    }
//                                $str .= "],
//                                borderWidth: 1
//                            }]
//                        },
//                        options: {
//                            scales: {
//                                yAxes: [{
//                                    ticks: {
//                                        beginAtZero:true
//                                    }
//                                }]
//                            }
//                        }
//                    });
//                });
//            </script>
//        </div>
//    </div>";
//

//            return $str;
//        });
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
