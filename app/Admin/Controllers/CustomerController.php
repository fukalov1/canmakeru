<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\ExportOneFgis;
use App\Admin\Actions\Post\Slave;
use App\AdminConfig;
use App\Customer;
use App\Pressure;
use App\Protokol;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use PHPExcel;
use PHPExcel_IOFactory;
use Illuminate\Support\Facades\Storage;
use Zip;
use Illuminate\Support\Facades\Log;

class CustomerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Партнеры';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Customer());

        $grid->perPages([50, 100, 200, 500]);
        $grid->paginate(100);

        $grid->filter(function($filter){

            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $filter->like('name', 'ФИО');
            $filter->like('comment', 'Комментарий');
            $filter->like('code', 'Код клиента');
            $filter->like('partner_code', 'Код партнера');
            $filter->like('email', 'E-mail');
            $filter->like('acts.number_act', 'Номер акта');
            $filter->like('protokols.protokol_num', 'Номер свидетельства');
            $filter->like('protokols.serialNumber', 'Заводской номер');
            $filter->equal('enabled')->radio([
                ''   => 'Все',
                0    => 'Не активны',
                1    => 'Активны',
            ]);
            $filter->in('export_fgis', 'Выгружать во ФГИС')->radio([
                '1'    => 'да',
                '0'    => 'нет',
            ]);

        });

        $grid->header(function ($query) {
            return '<a href="/admin/export-fgis" target="_blank">Экспорт XML для ФГИС</a>';
        });

        if (Admin::user()->roles[0]->slug!='administrator') {
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableView();
            });
        }
        else {
            $grid->actions(function ($actions) {
                $actions->add(new Slave());
                $actions->add(new ExportOneFgis());
            });
        }

        $grid->column('code', __('UID'));
        $grid->column('partner_code', __('Код партнера'))->sortable();
        $grid->name('ФИО')->display(function () {
            return '<a href="/admin/acts?set='.$this->id.'" title="Акты с поверками клиента '.$this->name.'">'.$this->name.'</a>';
        })->sortable();

        $grid->amount('Расходы')->display(function () {
            return '<a href="/admin/transactions?set='.$this->id.'" title="Транзакции '.$this->name.'">'.$this->amount.'</a>';
        });

        $grid->limit('Лимит')->display(function () {
            return '<a href="/admin/checks?set='.$this->id.'" title="Чеки '.$this->name.'">'.$this->limit.'</a>';
        });

        $grid->dinamic('Динамика поверок')->display(function () {
            return '<a href="/admin/customer_chart?set='.$this->id.'" title="Динамика поверок "><span class="fa fa-bar-chart"/></a>';
        });
        $grid->column('comment', __('Комментарий'))->sortable();
        $grid->column('enabled', __('Активен'))->display(function () {
            return $this->enabled ? "&#10004;" : '';
        });
//        $grid->column('export_fgis', __('Выгружать во ФГИС'))->icon([
//            0 => '',
//            1 => 'check',
//        ], $default = '');
        $grid->column('export_fgis', __('Выгружать во ФГИС'))->display(function () {
            return $this->export_fgis >0  ? "&#10004;" : '';
        });
        $grid->column('email', __('E-mail'))->sortable();

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
        $show->field('comment', __('Комментарий'));
        $show->field('enabled', __('Активен'));
        $show->field('email', __('E-mail'));

        $show->slave_customers('Работники', function ($slave_customers) {

            $slave_customers->resource('/admin/slave_customers');

            $slave_customers->id();
            $slave_customers->slave_id('ФИО')->display(function ($slave_id) {
                return Customer::find($slave_id)->name;
            });

            $slave_customers->filter(function ($filter) {
                $filter->like('slave_id');
            });
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Customer());

        $form->tab('Данные партнера', function ($form) {
            $form->text('code', __('Код'))->rules(function ($form) {
                if (!$id = $form->model()->id) {
                    return 'unique:customers';
                }
            });
            $form->number('partner_code', ' Код партнера')->default(0);
            $form->text('name', __('ФИО'))->rules(function ($form) {
                if (!$id = $form->model()->id) {
                    return 'unique:customers';
                }
            });

            $form->radio('type', 'Тип')->options(['ИП' => 'ИП', 'Самозанятый'=> 'Самозанятый', 'Физ.лицо'=> 'Физ.лицо'])->default('ИП')->stacked();
            $form->switch('check_online', 'Онлайн-касса')->default('0');
            $form->number('blank_price', ' Стоимость бланка')->default(120);

            $form->text('comment', __('Комментарий'));
            $form->switch('enabled', __('Активен'))->default(1);
            $form->number('hour_zone', 'Часовой пояс')->default(0);
            $form->switch('export_fgis', __('Выгружать во ФГИС'))->default(1);
            $form->email('email', __('E-mail'))->rules(function ($form) {
                if (!$id = $form->model()->id) {
                    return 'unique:customers';
                }
            });
            $form->text('get', __('ГЭТ'));
            $form->text('ideal', __('Эталон'));
            $form->text('ci_as_ideal', __('СИ, как эталон'));
            $form->text('notes', __('Примечание'));
            $form->hidden('password');

        })->tab('Работники', function ($form) {
            $form->hasMany('slave_customers', 'Работники', function (Form\NestedForm $form) {
                $form->select('slave_id', 'ФИО')->options(function ($id) {
                    $customers = Customer::select('id', 'name')->get()->sortBy('name');
                    return $customers->pluck('name', 'id');
                });
            });
        })->tab('Средства измерения, применяемые при поверке', function ($form) {

            $form->hasMany('customer_tools', 'Средство измерения', function (Form\NestedForm $form) {
                $form->text('typeNum', 'Регистрационный номер типа СИ')->rules('required|max:128');
                $form->text('manufactureNum', 'Заводской номер СИ')->rules('required|max:128');
            });
        });


        $form->saving(function (Form $form) {
            if (!$form->password) {
                $form->password = '123456';
            }
        });


        return $form;
    }

    private function exportToFGIS()
    {
        $protokols = collect([]);
        $customers = Customer::where('export_fgis',1)->get();
        foreach ($customers as $customer) {
            foreach ($customer->protokols as $protokol) {
                $protokols->push($protokol);
            }
        }
        $this->createCsv($protokols, 'main_meta');
    }

    private function createCsv($modelCollection, $tableName){

        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // This creates header columns in the CSV file - probably not needed in some cases.
        $csv->insertOne(Schema::getColumnListing($tableName));

        foreach ($modelCollection as $data){
            $csv->insertOne($data->toArray());
        }

        $csv->output($tableName . '.csv');

    }

    public function exportXmlToFGIS()
    {

        $package_number = $this->updatePackageNumber();

        $date = date('Y-m-d', time());
        $headers = array(
            'Content-Type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="poverka'.$date.'.xml"',
        );

        $protokols = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<application xmlns:gost=\"urn://fgis-arshin.gost.ru/module-verifications/import/2020-06-19\">\n";

        $customers = Customer::where('export_fgis',1)->get();


        foreach ($customers as $customer) {
            // подготовливаем xml по результатам поверок
            $protokols .= $this->prepareData($customer, $package_number);
        }
        $protokols .= "</application>";

        return response()->stream(function () use ($protokols)  {
            echo $protokols;
        }, 200, $headers);

    }

    public function exportOneXmlToFGIS($id)
    {

        $package_number = $this->updatePackageNumber();

        $date = date('Y-m-d', time());
        $headers = array(
            'Content-Type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="poverka'.$date.'.xml"',
        );

        $protokols = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<application xmlns:gost=\"urn://fgis-arshin.gost.ru/module-verifications/import/2020-06-19\">\n";

        $customer = Customer::find($id);

        // подготовливаем xml по результатам поверок
        $protokols .= $this->prepareData($customer, $package_number);

        $protokols .= "</application>";

        return response()->stream(function () use ($protokols)  {
            echo $protokols;
        }, 200, $headers);

    }

    /***
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * повторная пакетная выгрузка данных о поверках
     * во ФГИС Аршин по номеру пакета за указанный период
     */
    public function exportPackageXmlToFGIS()
    {
        $package_number = \request()->package_number;
        $date1 = \request()->date1;
        $date2 = \request()->date2;

        $date = date('Y-m-d', time());
        $headers = array(
            'Content-Type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="poverka'.$date.'.xml"',
        );
        $file_name = "poverka{$package_number}-$date";
        $protokol_head = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<application xmlns=\"urn://fgis-arshin.gost.ru/module-verifications/import/2020-06-19\">\n";
        $protokol_footer = "</application>";

        $protokols = Protokol::join('customers', 'customers.id', 'protokols.customer_id')
            ->where('customers.export_fgis',1)
            ->where('protokols.exported', $package_number);

        $protokols = Protokol::with(['customer' => function($q) {
            $q->where('customers.export_fgis', 1);
            }])
            ->where('protokols.exported', $package_number);

        if ($date1 and $date2) {
            $protokols = $protokols
                ->where('protokol_dt', '>=', "$date1 00:00:00")
                ->where('protokol_dt', '<=', "$date2 23:59:59");
        }


        // получаем новый номер пакета для выгрузки нулевых св-в при повторной загрузке за период
        $package_update = false;
        if ($package_number==0) {
            $package_update = true;
            $package_number = $this->updatePackageNumber();
        }

        $protokols = $protokols->get();

        Log::info('Export fgis. Select records: '. $protokols->count());

        $xml_records = config('xml_records', 4300);
        if ($protokols->count() >= $xml_records) {

            $this->refreshDir(storage_path('app/temp/'.$package_number));

//            $protokols = $protokols->chunk($xml_records)->all();

            $i = 1;
            $result = '';
            $data = $protokols->chunk($xml_records);
//            $protokols->chunk($xml_records, function ($items) use ($package_number, $package_update, $result, $protokol_head, $protokol_footer, $file_name, $i) {
            foreach ($data as $items) {
                $result = $protokol_head;
                // подготовливаем xml по результатам поверок
                foreach ($items as $item) {
                    if ($item->regNumber) {
//                        echo "$i = {$item->protokol_num}<br>\n";
                        $result .= $this->getXml2Fgis($item, $package_number, $package_update);
                    }
                }
                $result .= $protokol_footer;
                Storage::disk('local')->put('/temp/' . $package_number . '/' . $file_name . "-$i.xml", $result);
                Log::info('Export fgis. Fill file: '. $file_name . "-$i.xml");
                ++$i;
            };
//            dd($i);

            if (file_exists(storage_path('app/temp/') . "$file_name.zip")) {
                Log::info('Export fgis. Unlink file: '. $file_name . "zip");
                unlink(storage_path('app/temp/') . "$file_name.zip");
            }

//            if ($i > 1) {
                $zip = Zip::create(storage_path('app/temp/') . "$file_name.zip");
                $zip->add(storage_path("app/temp/$package_number"), true);
                $zip->close();
                Log::info('Export fgis. Create file: '. $file_name . ".zip");
//            }

            $fileurl = storage_path('app/temp/')."$file_name.zip";

            if (file_exists($fileurl)) {
                $headers = array(
                    'Content-Type' => 'application/octet-stream',
                    'Content-Disposition' => 'attachment; filename="'.$file_name.'.zip"',
                );
                return response()->file($fileurl, $headers)->deleteFileAfterSend(true);
            } else {
                return ['status'=>'empty set'];
            }

        }
        else {
            $result = $protokol_head;
            // подготовливаем xml по результатам поверок
            foreach ($protokols as $protokol) {
                $result .= $this->getXml2Fgis($protokol, $package_number, $package_update);
            }
            $result .= $protokol_footer;
            Storage::disk('local')->put('/temp/' . $file_name . ".xml", $result);

            return response()->download(storage_path('app/temp/')."$file_name.xml", "$file_name.xml", $headers);
        }

    }

    private function refreshDir($dir) {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
            }
            rmdir($dir);
        }
        mkdir($dir);
        return true;
    }

    private function prepareData($customer, $package_number, $type = 'new', $date1 = null, $date2 = null)
    {
        $result = '';
        $package_update = false;

        // выбираем поверки клиента либо новые, либо из пакета
        if ($type == 'new') {
            $new_protokols = $customer->new_protokols;
            $package_update = true;
        }
        else if ($type == 'exist') {
            $new_protokols = $customer->protokols
                ->where('exported', $package_number);

            if ($date1 and $date2) {
                $new_protokols = $new_protokols->where('protokol_dt', '>', "$date1 00:00:00")
                    ->where('protokol_dt', '<=', "$date2 23:59:59");
            }

            // получаем новый номер пакета для выгрузки нулевых св-в при повторной загрузке за период
            if ($package_number==0) {
                $package_update = true;
                $package_number = $this->updatePackageNumber();
            }
        }

        $new_protokols = $new_protokols;

        foreach ($new_protokols as $protokol) {
            $result = $this->getXml2Fgis($protokol, $package_number, $package_update);
        }

        return $result;
    }

    private function getXml2Fgis($protokol, $package_number, $package_update = false)
    {
        $result = '';
                $pressure = $this->getPressure($protokol->customer->id, date('Y-m-d', strtotime($protokol->protokol_dt)));

                $result .= "\t<result>\n";

                $result .= "\t\t<miInfo>
                    <singleMI>
                            <mitypeNumber>" . $protokol->regNumber . "</mitypeNumber>
                            <manufactureNum>" . $protokol->serialNumber . "</manufactureNum>
                            <modification>" . $protokol->siType . "</modification>
                    </singleMI>
                </miInfo>\n";

                $nextTest = null;
                if ((int)$protokol->checkInterval > 0) {
                    $nextTest = strtotime("+$protokol->checkInterval YEAR", strtotime($protokol->protokol_dt));
                    $nextTest = strtotime('-1 DAYS', $nextTest);
                    $nextTest = date("Y-m-d", $nextTest);
                }

                $hour_zone = sprintf('+%02d:00', $protokol->customer->hour_zone);
                //dd($customer->hour_zone, $hour_zone);

                $result_test = "<applicable>
                    <signPass>false</signPass>
                    <signMi>false</signMi>
                    </applicable>";
                if ($protokol->act->type === 'непригодны') {
                    $result_test = "<inapplicable>
                            <reasons>Не соответствует метрологическим требованиям</reasons>
                        </inapplicable>";
                }

                $result .= "\t\t<signCipher>" . config('signCipher', 'ГСЧ') . "</signCipher>
                    <miOwner>" .$protokol->act->miowner. "</miOwner>
                    <vrfDate>" .date("Y-m-d",strtotime($protokol->protokol_dt)) .$hour_zone. "</vrfDate>
                    <validDate>" . $nextTest .$hour_zone. "</validDate>
                    <type>2</type>
                    <calibration>false</calibration>
                    ".$result_test."
                    <docTitle>" . $protokol->checkMethod . "</docTitle>\n";

                $result .= "\t\t<means>\n";

                if ($protokol->customer->ideal) {
                    $ideal = $protokol->customer->ideal ? $protokol->customer->ideal : '3.2.ВЮМ.0023.2019';
                    $result .= "\t\t\t<uve>
                                <number>$ideal</number>
                        </uve>\n";
                }
                else if ($protokol->customer->ci_as_ideal) {
                    $result .= "\t\t\t<mieta>
                                <number>{$protokol->customer->ci_as_ideal}</number>
                        </mieta>\n";
                }
                else if ($protokol->customer->ci_as_ideal_fake) {
                    $result .= "\t\t\t<mieta>
                                <number>{$protokol->customer->ci_as_ideal_fake}</number>
                        </mieta>\n";
                }
                else if ($protokol->customer->get) {
                    $result .= "\t\t\t<npe>
                                <number>{$protokol->customer->get}</number>
                        </npe>\n";
                }
                if(!$protokol->customer->customer_tools) {
                    $result .= "\t\t\t<mis>\n";
                    foreach ($protokol->customer->customer_tools as $customer_tool) {

                        $result .= "\t\t\t\t<mi>
                                <typeNum>{$customer_tool->typeNum}</typeNum>
                                <manufactureNum>{$customer_tool->manufactureNum}</manufactureNum>
                            </mi>\n";
                    }
                    $result .= "\t\t\t</mis>\n";
                }

                $result .= "\t\t</means>\n";

                $result .= "\t\t<conditions>\n";
                $result .= "\t\t\t<temperature>".$protokol->act->temperature."</temperature>\n";
                $result .= "\t\t\t<pressure>$pressure</pressure>\n";
                $result .= "\t\t\t<hymidity>".$protokol->act->hymidity."</hymidity>\n";
//                if ($protokol->type_water=='XB') {
//                    $result .= "\t\t\t<cold_water>$cold_water</cold_water>\n";
//                }
//                else {
//                    $result .= "\t\t\t<hot_water>$hot_water</hot_water>\n";
//                }
                $result .= "\t\t</conditions>\n";

                if ($protokol->customer->notes) {
                    $result .= "<additional_info>{$protokol->customer->notes}</additional_info>";
                }

                $result .= "\t</result>\n";

                if ($package_update) {
                    try {
                        Protokol::find($protokol->id)
                            ->update(['exported' => $package_number]);
                        Log::info("Export fgis. Update protokol: {$protokol->id} ({$protokol->protokol_num}) with $package_number number.");
                    }
                    catch (\Throwable $exception) {
                        Log::error("Export fgis. Error update protokol: {$protokol->id} ({$protokol->protokol_num}) with $package_number number.");
                    }
        }
        return $result;
    }

    public function convertXlsToXml(Request $request)
    {
        $date = date('Y-m-d', time());
        $headers = array(
            'Content-Type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="poverka'.$date.'.xml"',
        );

        $protokols = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<application xmlns:gost=\"urn://fgis-arshin.gost.ru/module-verifications/import/2020-04-14\">\n";




        if (request()->file('file_xls')) {
            $path = request()->file('file_xls')->getRealPath();

            $objPHPExcel = PHPExcel_IOFactory::load($path);
            $sheet = $objPHPExcel->getSheet(0);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $highestRow = $objWorksheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            $data = [];
            for ($row = 2; $row <= $highestRow; ++$row) {
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                    NULL,
                    TRUE,
                    TRUE);
                if ($rowData[0][0] == '') {
                    break;
                }
                $data[] = $rowData[0];
            }

            foreach ($data as $protokol) {
                if ($protokol[0]) {

                    $protokols .= "\t<result>\n";

                    $protokols .= "\t\t<miInfo>
                    <singleMI>
                            <mitypeNumber>" . $protokol[0] . "</mitypeNumber>
                            <manufactureNum>" . $protokol[2] . "</manufactureNum>
                            <modification>" . $protokol[1] . "</modification>
                    </singleMI>
                </miInfo>\n";


                    $hour_zone = sprintf('+0%d:00', $protokol[13]);
//dd($protokol[3], strtotime($protokol[3]));
                    $protokols .= "\t\t<signCipher>" . $protokol[14] . "</signCipher>
                    <vrfDate>" .date("Y-m-d",strtotime($protokol[3])).$hour_zone. "</vrfDate>
                    <validDate>" . date("Y-m-d",strtotime($protokol[4])) .$hour_zone. "</validDate>
                    <applicable>
                            <certNum>" . $protokol[7] . "</certNum>
                            <signPass>false</signPass>
                            <signMi>false</signMi>
                    </applicable>
                    <docTitle>" . $protokol[5] . "</docTitle>\n";

                    $protokols .= "\t\t<means>\n";

                    if ($protokol[8]) {
                        $protokols .= "\t\t\t<npe>
                                <number>$protokol[8]</number>
                        </npe>\n";
                    }
                    else if ($protokol[9]) {
                        $protokols .= "\t\t\t<uve>
                                <number>$protokol[9]</number>
                        </uve>\n";
                    }
                    else if ($protokol[10]) {
                        $protokols .= "\t\t\t<mieta>
                                <number>{$protokol[10]}</number>
                        </mieta>\n";
                    }

                    $protokols .= "\t\t\t<mis>\n";
                    $customer_tools = explode('|', $protokol[11]);
                    foreach ($customer_tools as $customer_tool) {
//                        dd($customer_tool);
                        $item = explode(',', $customer_tool);
//                        dd($item);
                        if (is_array($item)) {
                            if (count($item)==2) {
                                $protokols .= "\t\t\t\t<mi>
                                <typeNum>{$item[0]}</typeNum>
                                <manufactureNum>{$item[1]}</manufactureNum>
                            </mi>\n";
                            }
                        }
                    }
                    $protokols .= "\t\t\t</mis>\n";

                    $protokols .= "\t\t</means>\n";

                    if ($protokol[12]) {
                        $protokols .= "<additional_info>{$protokol[12]}</additional_info>";
                    }

                    $protokols .= "\t</result>\n";
                }
            }

            $protokols .= "</application>";
//dd($protokols);

            return response()->stream(function () use ($protokols)  {
                echo $protokols;
            }, 200, $headers);

        }
        else {
            dd('error');
            return back()->with('error', 'Excel file is empty.');
        }
    }

    private function getProtokolNumber($protokol_num)
    {
        if ($protokol_num) {
            return intval(substr($protokol_num, 0, -7)) . '-' . intval(substr($protokol_num, -7, 2)) . '-' . intval(substr($protokol_num, -5));
        }
        else {
            return '';
        }
    }

    private function updatePackageNumber()
    {
        $package_number = config('package_number', 1);
        $package_number++;
        $admin_config = AdminConfig::where('name', 'package_number')->update(['value' => $package_number]);

        return $package_number;
    }

    private function getPressure($customer_id, $date)
    {
        $result = rand(1008, 1019)/10;
        $pressure = Pressure::where('customer_id', $customer_id)
            ->where('date', $date )->first();
        if ($pressure) {
            $result = $pressure->value;
        }
        return $result;
    }

}
