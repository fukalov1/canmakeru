<?php

namespace App\Admin\Controllers;

use App\Customer;
use App\Protokol;
use App\SlaveCustomer;
use DemeterChain\C;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Actions\Post\Slave;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Collection;
use League\Csv\Writer;
use Schema;
use SplTempFileObject;


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
        $grid = new Grid(new Customer);
        $grid->filter(function($filter){

            // Remove the default id filter
            $filter->disableIdFilter();

            // Add a column filter
            $filter->like('name', 'ФИО');
            $filter->like('comment', 'Комментарий');
            $filter->like('code', 'Код клиента');
            $filter->like('email', 'E-mail');
            $filter->like('protokols.protokol_num', 'Номер свидетельства');
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
            });
        }

        $grid->column('code', __('Код'));
        $grid->name('ФИО!
        ')->display(function () {
            return '<a href="/admin/protokols?set='.$this->id.'" title="Поверки клиента '.$this->name.'">'.$this->name.'</a>';
        })->sortable();
        $grid->dinamic('Динамика поверок')->display(function () {
            return '<a href="/admin/customer_chart?set='.$this->id.'" title="Динамика поверок "><span class="fa fa-bar-chart"/></a>';
        });
        $grid->column('comment', __('Комментарий'));
        $grid->column('enabled', __('Активен'))->icon([
            0 => '',
            1 => 'check',
        ], $default = '');
        $grid->column('export_fgis', __('Выгружать во ФГИС'))->icon([
            0 => '',
            1 => 'check',
        ], $default = '');
        $grid->column('email', __('E-mail'));

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
        $form = new Form(new Customer);

        $form->tab('Данные партнера', function ($form) {
            $form->text('code', __('Код'))->rules(function ($form) {
                if (!$id = $form->model()->id) {
                    return 'unique:customers';
                }
            });
            $form->text('name', __('ФИО'))->rules(function ($form) {
                if (!$id = $form->model()->id) {
                    return 'unique:customers';
                }
            });
            $form->text('comment', __('Комментарий'));
            $form->switch('enabled', __('Активен'))->default(1);
            $form->number('hour_zone', 'Временная зона (по Москве)')->default(0);
            $form->switch('export_fgis', __('Выгружать во ФГИС'))->default(1);
            $form->email('email', __('E-mail'))->rules(function ($form) {
                if (!$id = $form->model()->id) {
                    return 'unique:customers';
                }
            });
            $form->text('ideal', __('Эталон'));
            $form->text('get', __('ГЭТ'));
            $form->radio('type_ideal', 'Тип эталона')->options(
                [
                    'эталон' => 'эталон',
                    'не утвержденный' => 'не утвержденный',
                    'СИ, как эталон' => 'СИ, как эталон'
                ]
            )->value('не утвержденный');

//        })->tab('Работники', function ($form) {
//         $form->hasMany('slave_customers', 'Работники', function (Form\NestedForm $form) {
//            $form->select('slave_id', 'ФИО')->options(function ($id) {
//                $customers = Customer::select('id','name')->get()->sortBy('name');
//                return $customers->pluck('name', 'id');
//            });
//          });
        })->tab('Средства измерения, применяемые при поверке', function ($form) {

            $form->hasMany('customer_tools', 'Средство измерения', function (Form\NestedForm $form) {
                $form->text('typeNum', 'Регистрационный номер типа СИ')->rules('required|max:128');
                $form->text('manufactureNum', 'Заводской номер СИ')->rules('required|max:128');
            });
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
        $headers = array(
            'Content-Type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="poverka.xml"',
        );

        $protokols = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n<gost:application xmlns:gost=\"urn://fgis-arshin.gost.ru/module-verifications/import/2020-04-14\">\n";

        $customers = Customer::where('export_fgis',1)->get();


        foreach ($customers as $customer) {
            foreach ($customer->new_protokols as $protokol) {
                if ($protokol->regNumber) {

                    $protokols .= "\t<gost:result>\n";

                    $protokols .= "\t\t<gost:miInfo>
                    <gost:singleMI>
                            <gost:mitypeNumber>" . $protokol->regNumber . "</gost:mitypeNumber>
                            <gost:manufactureNum>" . $protokol->siType . "</gost:manufactureNum>
                            <gost:modification>" . $protokol->serialNumber . "</gost:modification>
                    </gost:singleMI>
                </gost:miInfo>\n";

                    $nextTest = null;
                    if ((int)$protokol->checkInterval > 0) {
                        $nextTest = strtotime("+$protokol->checkInterval YEAR", strtotime($protokol->protokol_dt));
                        $nextTest = strtotime('-1 DAYS', $nextTest);
                        $nextTest = date("Y-m-d H:i:s", $nextTest);
                    }

                    $protokols .= "\t\t<gost:signCipher>" . config('signCipher', 'ГСЧ') . "</gost:signCipher>
                    <gost:vrfDate>" . $protokol->protokol_dt . "</gost:vrfDate>
                    <gost:validDate>" . $nextTest . "</gost:validDate>
                    <gost:applicable>
                            <gost:certNum>" . $this->getProtokolNumber($protokol->protokol_num) . "</gost:certNum>
                            <gost:signPass>false</gost:signPass>
                            <gost:signMi>false</gost:signMi>
                    </gost:applicable>
                    <gost:docTitle>" . $protokol->checkMethod . "</gost:docTitle>\n";

                    $protokols .= "\t\t<gost:means>\n";

                    if ($customer->type_ideal == 'эталон' or $customer->type_ideal==null) {
                        $ideal = $customer->ideal ? $customer->ideal : '3.2.ВЮМ.0023.2019';
                        $protokols .= "\t\t\t<gost:uve>
                                <gost:number>$ideal</gost:number>
                        </gost:uve>\n";
                    }
                    if ($customer->type_ideal == 'СИ, как эталон') {
                        $protokols .= "\t\t\t<gost:mieta>
                                <gost:number>{$customer->ideal}</gost:number>
                        </gost:mieta>\n";
                    }

                    foreach ($customer->customer_tools as $customer_tool) {
                        $protokols .= "\t\t\t<gost:mis>
                            <gost:mi>
                                <gost:typeNum>{$customer_tool->typeNum}</gost:typeNum>
                                <gost:manufactureNum>{$customer_tool->manufactureNum}</gost:manufactureNum>
                            </gost:mi>
                        </gost:mis>\n";
                    }

                    $protokols .= "\t\t</gost:means>\n";

                    if ($customer->type_ideal == 'не утвержденный') {
                        $protokols .= "<gost:additional_info>{$customer->ideal}</gost:additional_info>";
                    }

                    $protokols .= "\t</gost:result>\n";
                }
            }
            Protokol::where('customer_id', $customer->id)
                ->update(['exported' => 1]);
        }
        $protokols .= "</gost:application>";

        return response()->stream(function () use ($protokols)  {
            echo $protokols;
        }, 200, $headers);

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

}
