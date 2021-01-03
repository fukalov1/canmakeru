<?php

namespace App\Admin\Controllers;

use App\Customer;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;

class CustomerChartController extends Controller
{

    public $title = 'Динамика поверок счетчиков ';
    public $customer_name='';

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        $this->getHeader();
        return Admin::content(function (Content $content) {

            $id = session('customer_id');
            $quest  = Customer::join('protokols','customers.id','protokols.customer_id')
                ->select(\DB::raw('date_format(protokols.protokol_dt, "%Y-%m") as date, count(protokols.protokol_num) count'))
                ->whereRaw('date_format(protokol_dt, "%Y-%m") <> "0000-00"')
                ->where('customer_id', $id)
                ->groupBy(\DB::raw('date_format(protokol_dt, "%Y-%m")'))
                ->get()->toArray();

            $content->header($this->customer_name);
            $content->description($this->title);


            $data = ['dinamic' => $quest];

            $content->body(view('admin.charts.customer', $data));
        });
    }

    public function getHeader()
    {
        $customers = Customer::find(session('customer_id'));
//        dd($customers->name);
        $this->customer_name = $customers->name;
        $this->title .= ' - '.$customers->name;
//            dd($this->title);
    }


}
