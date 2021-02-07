<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Protokol\ReportExport;
use App\Customer;
use App\Act;
use App\Protokol;
use Illuminate\Support\Facades\DB;
use App\Exports\CustomerExport;
use App\Exports\CustomerExportXml;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
//use Maatwebsite\Excel\Facades\Excel;
use App\Admin\Extensions\ExcelExpoter;


class CustomerReport1Controller extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Экспорт в XLS за период';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Customer);

        $grid->header(function ($query) {
            if (isset($_SERVER['QUERY_STRING'])) {
                $url = $_SERVER['QUERY_STRING'];
                return "<div style='padding: 10px;'><a href=\"/admin/customer_report1/export?$url\" title='экспорт в Excel' target='_blank'>Экспорт в Excel</a> </div>";
            }
        });

//        $grid->export(function ($export) {
//            $export->filename('Filename.csv');
//            $export->originalValue(['name', 'partner_code', 'protokols']);
//        });

        $grid->exporter(new ExcelExpoter());
//        $grid->export(function ($export) {
//
//            $export->filename('Filename.csv');
//            $export->only(['name', 'protokols', 'act_count', 'act_good', 'act_bad', 'act_brak']);
//
//        });

        $grid->filter(function($filter){
            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $filter->like('name', 'ФИО');
            $filter->like('code', 'Код клиента');
            $filter->between('acts.date', 'Период')->date();

        });

        $grid->disableActions();


        $grid->column('partner_code', __('Код партнера'));
        $grid->column('name', 'ФИО');
        $grid->protokols('Кол-во поверок')->display(function ($protokols) {
            if (!!request('protokols')) {
                $start = (request('acts')['date']['start']);
                $end = (request('acts')['dare']['end']);
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


    public function export()
    {

        $params = request()->input('acts');
        $start = $params['date']['start'];
        $end = $params['date']['end'];

        $filename = time().'.csv';
        $output = '';
        try {
//            if ($fh = fopen(storage_path('admin') . $filename, "w+")) {
//                $acts = \DB::select("select customers.name, (select count(id) from acts where customer_id=customers.id and acts.date>=\"2021-01-01 00:00:00\" and acts.date<=\"2021-01-03 23:59:59\" and acts.name<>'Нулевой') act_count, (select count(id) from acts where type='пригодны' and customer_id=customers.id and acts.date>=\"2021-01-01 00:00:00\" and acts.date<=\"2021-01-03 23:59:59\" and acts.name<>'Нулевой') act_good, (select count(id) from acts where type='непригодны' and customer_id=customers.id and acts.date>=\"2021-01-01 00:00:00\" and acts.date<=\"2021-01-03 23:59:59\" and acts.name<>'Нулевой') act_bad, (select count(id) from acts where type='испорчен' and customer_id=customers.id and acts.date>=\"2021-01-01 00:00:00\" and acts.date<=\"2021-01-03 23:59:59\" and acts.name<>'Нулевой') act_brak from customers, acts where customers.id=acts.customer_id and acts.date>=\"2021-01-01 00:00:00\" and acts.date<=\"2021-01-03 23:59:59\" and acts.name<>'Нулевой' group by 'customers.id'");
                $customers = DB::table('customers')
                    ->join('acts', 'customers.id', 'acts.customer_id')
                    ->select('customers.id', 'partner_code', 'customers.name',  DB::raw('count(*) as count'))
                    ->whereBetween('date', [$start." 00:00:00", $end." 23:59:59"])
                    ->groupBy('customers.id')->get();

//                dd($customers, $start." 00:00:00", $end." 23:59:59");
                foreach ($customers as $item) {

                    $acts = Act::where('customer_id', $item->id)
                        ->whereBetween('date', [$start, $end])
                        ->with('meters')
                        ->get();
                    $protokols = 0;
                    foreach ($acts as $act) {
                        $protokols =+ $act->meters->count();
                    }

                    $good = Act::where('customer_id', $item->id)
                        ->whereBetween('date', [$start, $end])
                        ->where('type', 'пригодны')
                        ->get()->count();
                    $bad = Act::where('customer_id', $item->id)
                        ->whereBetween('date', [$start, $end])
                        ->where('type', 'непригодны')
                        ->get()->count();
                    $brak = Act::where('customer_id', $item->id)
                        ->whereBetween('date', [$start, $end])
                        ->where('type', 'испорчен')
                        ->get()->count();

                    $output .= "{$item->partner_code};{$item->name};$protokols;{$item->count};{$good};{$bad};{$brak}\n";

                }
//                fwrite($fh, $output);
                // This logic get the columns that need to be exported from the table data
//                $rows = collect($this->getData())->map(function ($item) {
//                    return $item;
//                });
//                fclose($fh);
//            }
        }
        catch (\Throwable $exception) {
            dd($exception->getMessage());
        }
//        dd($output. 'test');

        $headers = [
            'Content-Encoding'    => 'UTF-8',
            'Content-Type'        => 'text/csv;charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        response(rtrim($output, "\n"), 200, $headers)->send();


    }


}
