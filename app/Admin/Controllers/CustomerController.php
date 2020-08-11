<?php

namespace App\Admin\Controllers;

use App\Customer;
use App\SlaveCustomer;
use DemeterChain\C;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Actions\Post\Slave;
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
            return '<a href="/admin/export-fgis" target="_blank">выгрузить данные для ФГИС</a>';
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

        $form->text('code', __('Код'))->required(true);;
        $form->text('name', __('ФИО'));
        $form->text('comment', __('Комментарий'));
        $form->switch('enabled', __('Активен'))->default(1);
        $form->switch('export_fgis', __('Выгружать во ФГИС'))->default(1);
        $form->email('email', __('E-mail'))->required(true);
        $form->text('ideal', __('Эталон'));
        $form->text('get', __('ГЭТ'));
        $form->select('type_ideal', 'Тип эталона')->options(
            [
                0 => 'Эталон',
                1 => 'Не утвержденный',
                2 => 'СИ, как эталон',
            ]
        );

//        $form->hasMany('slave_customers', 'Работники', function (Form\NestedForm $form) {
//            $form->select('slave_id', 'ФИО')->options(function ($id) {
//                $customers = Customer::select('id','name')->get()->sortBy('name');
//                return $customers->pluck('name', 'id');
//            });
//        });

        $form->hasMany('customer_tools', 'Средства измерения, применяемые при поверке', function (Form\NestedForm $form) {
            $form->text('typeNum', 'Регистрационный номер типа СИ');
            $form->text('manufactureNum', 'Заводской номер СИ');
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

    private function exportXmlToFGIS()
    {

        $file = '<?xml version="1.0" encoding="utf-8" ?>\n\t
            <gost:application xmlns:gost="urn://fgis-arshin.gost.ru/module-verifications/import/2020-04-14">\n';

        $protokols = collect([]);
        $customers = Customer::where('export_fgis',1)->get();
        foreach ($customers as $customer) {
            foreach ($customer->protokols as $protokol) {
                $protokols->push($protokol);
//                $file .= "$protokol->siType;$protokol->regNumber;;;;1;$protokol->serialNumber;;2020-03-01;2026-02-28;Нет данных;МИ 1592-2015;Пригодно;110-20-6;;;;;гэт63-2017;;\n";
            }
        }
        $headers = array(
            'Content-Type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="poverka.xml"',
        );

            foreach ($protokols as $protokol) {
                $row['siType']  = $protokol->siType;
                $row['regNumber']    = $protokol->regNumber;
                $row['serialNumber']    = $protokol->serialNumber;
                $row['createdAt']  = $protokol->created_at;
                $row['nextTest']  = $protokol->nextTest;

                $file .= '	<gost:result>
		<gost:miInfo>
			<gost:etaMI>
				<gost:primaryRec>
					<gost:mitypeNumber>76062-19</gost:mitypeNumber>
					<gost:modification>DS1102E</gost:modification>
					<gost:manufactureNum>1205-2019</gost:manufactureNum>
					<gost:manufactureYear>2019</gost:manufactureYear>
 					<gost:isOwner>true</gost:isOwner>
 					<gost:gps>
 						<gost:title>Государственная поверочная схема для средств измерений массы</gost:title>
 						<gost:npeNumber>гэт3-2008</gost:npeNumber>
 						<gost:rank>4Р</gost:rank>
 					</gost:gps>
 				</gost:primaryRec>
 			</gost:etaMI>
 		</gost:miInfo>
 		<gost:signCipher>ЭЭЭ</gost:signCipher>
 		<gost:vrfDate>2020-05-12+03:00</gost:vrfDate>
 		<gost:validDate>2021-05-11+03:00</gost:validDate>
 		<gost:applicable>
 			<gost:certNum>3457-2020</gost:certNum>
 			<gost:signPass>true</gost:signPass>
 			<gost:signMi>false</gost:signMi>
 		</gost:applicable>
 		<gost:docTitle>МП 2301-0179-2019</gost:docTitle>
 		<gost:means>
 			<gost:uve>
 				<gost:number>3.1.ZАР.0636.2018</gost:number>
 			</gost:uve>
 		</gost:means>
 		<gost:additional_info>Поверка 21-XML 2020-04-14</gost:additional_info>
 	</gost:result>';
            }

            $file .= '</gost:application>';
//            fclose($file);

        return response()->content($file);

    }
}
