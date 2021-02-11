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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        if ( request()->input('acts'))
            return $this->export();
        else {
            $grid = new Grid(new Customer);



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

            $grid->filter(function ($filter) {
                // Remove the default id filter
                $filter->disableIdFilter();

                // Add a column filter
                $filter->like('name', 'ФИО');
                $filter->like('code', 'ID поверителя');
                $filter->between('acts.date', 'Период')->datetime();

            });


                $grid->disableActions();

//        if (isset($_SERVER['QUERY_STRING'])) {
//
//            $params = request()->input('acts');
//            $start = $params['date']['start'];
//            $end = $params['date']['end'];
//
//
//            $grid->model()->join('acts', 'customers.id', 'acts.customer_id')
//                ->whereBetween('date', [$start." 00:00:00", $end." 23:59:59"]);
//        }


            $grid->column('partner_code', __('Код партнера'));
            $grid->column('name', 'ФИО');
//        $grid->protokols('Кол-во поверок')->display(function ($protokols) {
//            if (!!request('protokols')) {
//                $start = (request('acts')['date']['start']);
//                $end = (request('acts')['dare']['end']);
//                $protokols = collect($protokols);
//                $protokols = $protokols->filter(function ($item) use ($start,$end) {
//                    return $item['protokol_dt']>=$start and $item['protokol_dt']<=$end ;
//                });
//            }
//            return count($protokols);
//        });
            $grid->act_count('Кол-во актов')->display(function ($id) {
                return Customer::find($this->id)->acts()->count();
            });
            $grid->act_good('Пригодных')->display(function () {
                return Customer::find($this->id)->acts()->where('type', 'пригодны')->count();
            });
            $grid->act_bad('Непригодных')->display(function () {
                return Customer::find($this->id)->acts()->where('type', 'непригодны')->count();
            });
            $grid->act_brak('Испорченных')->display(function () {
                return Customer::find($this->id)->acts()->where('type', 'испорчен')->count();
            });


            return $grid;
        }
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

        $filename = time().'.xlsx';
//        $output = "Код партнера;Поверитель;Кол-во поверок;Всего актов;Пригодных;Непригодных;Испорченных\n";
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Код партнера');
            $sheet->setCellValue('B1', 'Поверитель');
            $sheet->setCellValue('C1', 'Кол-во поверок');
            $sheet->setCellValue('D1', 'Всего актов');
            $sheet->setCellValue('E1', 'Пригодных');
            $sheet->setCellValue('F1', 'Непригодных');
            $sheet->setCellValue('G1', 'Испорченных');

//            if ($fh = fopen(storage_path('admin') . $filename, "w+")) {
//                $acts = \DB::select("select customers.name, (select count(id) from acts where customer_id=customers.id and acts.date>=\"2021-01-01 00:00:00\" and acts.date<=\"2021-01-03 23:59:59\" and acts.name<>'Нулевой') act_count, (select count(id) from acts where type='пригодны' and customer_id=customers.id and acts.date>=\"2021-01-01 00:00:00\" and acts.date<=\"2021-01-03 23:59:59\" and acts.name<>'Нулевой') act_good, (select count(id) from acts where type='непригодны' and customer_id=customers.id and acts.date>=\"2021-01-01 00:00:00\" and acts.date<=\"2021-01-03 23:59:59\" and acts.name<>'Нулевой') act_bad, (select count(id) from acts where type='испорчен' and customer_id=customers.id and acts.date>=\"2021-01-01 00:00:00\" and acts.date<=\"2021-01-03 23:59:59\" and acts.name<>'Нулевой') act_brak from customers, acts where customers.id=acts.customer_id and acts.date>=\"2021-01-01 00:00:00\" and acts.date<=\"2021-01-03 23:59:59\" and acts.name<>'Нулевой' group by 'customers.id'");
                $customers = DB::table('customers')
                    ->join('acts', 'customers.id', 'acts.customer_id')
                    ->select('customers.id', 'partner_code', 'customers.name',  DB::raw('count(*) as count'))
                    ->whereBetween('date', [$start." 00:00:00", $end." 23:59:59"])
                    ->groupBy('customers.id')->get();

                $i=2;
//                dd($customers, $start." 00:00:00", $end." 23:59:59");
                foreach ($customers as $item) {

                    $acts = Act::where('customer_id', $item->id)
                        ->whereBetween('date', [$start, $end])
                        ->with(['meters' => function($q) use ($start, $end) {
                            $q->whereBetween('protokol_dt', [$start." 00:00:00", $end." 23:59:59"]);
                        }])
                        ->get();
                    $protokols = 0;
                    foreach ($acts as $act) {
                        $protokols += $act->meters->count();
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

                    $sheet->setCellValue('A'.$i, $item->partner_code);
                    $sheet->setCellValue('B'.$i, $item->name);
                    $sheet->setCellValue('C'.$i, $protokols);
                    $sheet->setCellValue('D'.$i, $item->count);
                    $sheet->setCellValue('E'.$i, $good);
                    $sheet->setCellValue('F'.$i, $bad);
                    $sheet->setCellValue('G'.$i, $brak);

//                    $output .= "{$item->partner_code};{$item->name};$protokols;{$item->count};{$good};{$bad};{$brak}\n";
                    $i++;
                }

            $writer = new Xlsx($spreadsheet);
            $writer->save(public_path('temp/'.$filename));

        }
        catch (\Throwable $exception) {
            dd($exception->getMessage());
        }
//        dd($output. 'test');

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename='$filename'"
        ];
        return "<a href='/temp/$filename' target='_blank'>Скачать файл Excel</a> / <a href='/admin/customer_report1'>назад</a>";
//        return response()->download(storage_path($filename), $filename, $headers)->deleteFileAfterSend();


    }


}
