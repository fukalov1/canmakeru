<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Protokol;
use Carbon\Traits\Date;
use Illuminate\Http\Request;
use PDF;
use Carbon\Carbon;

class CustomerController extends Controller
{

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function index()
    {
        return view('customer');
    }

    public function getCustomerId()
    {
        return json_encode(['customer_id' => auth()->guard('customer')->user()->id]);
    }

    public function getListWorkers()
    {
        return $this->customer->getWorkers();
    }

    public function getDataActs(Request $request)
    {
        $customer_id = $request->customer_id;
        if ($customer_id) {
            return $this->customer->find($customer_id)->acts()->get();
        }
            return [];
    }

    public function getDataProtokols(Request $request)
    {
        $customer_id = $request->customer_id;
        if ($customer_id) {
            return $this->customer->getProtokols($customer_id);
        }
        else {
            return [];
        }
    }

    public function getDataStatistic()
    {
        $customer_id = 0;
        if (request()->customer_id)
            $customer_id = request()->customer_id;
        return $this->customer->getDataChart($customer_id);
    }

    public function getReportDays()
    {
        $customer_id = 0;
        $start = request()->start;
        $end = request()->end;
        if (request()->customer_id)
            $customer_id = request()->customer_id;
        return $this->customer->getDataReportDays($customer_id, $start, $end);
    }

    public function checkProfile(Request $request)
    {
        $data = ['result' => 0,
            'message' => "Партнер с кодом {$request->id} не найден!"];

        if ($request->id == 'test_test_test') {
            $data['result'] = 1;
            $data['message'] = 'Демонстрационный идентификатор. Для работы зарегистрируйте личный кабинет.';
        }
        else {
            $customer = $this->customer
                ->with('customer_tools')
                ->where('code', $request->id)->first();

            if ($customer) {
                $data['result'] = 1;
                $data['message'] = $customer;
            }
        }
        return json_encode($data);
    }


    public function getProfile()
    {
        return json_encode($this->customer->find(auth()->guard('customer')->user()->id));
    }

    public function getPDF($id)
    {
//        PDF::setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif', 'setPaper'=> 'landscape']);
        $data = Protokol::find($id)->toArray();
        preg_match('/(\d\d\d\d)\-(\d\d)/', $data['protokol_dt'],$matches);
        $file = preg_replace('/photos\//','',$data['protokol_photo']);
        $data['protokol_photo'] = $matches[1].'/'.$matches[2].'/'.$file;
        $file = preg_replace('/photos\//','',$data['protokol_photo1']);
        $data['protokol_photo1'] = $matches[1].'/'.$matches[2].'/'.$file;
        $file = preg_replace('/photos\//','',$data['meter_photo']);
        $data['meter_photo'] = $matches[1].'/'.$matches[2].'/'.$file;

        $protokol_num = $data['protokol_num'];
        $data['protokol_num'] = intval(substr($protokol_num, 0,-7)).'-'.intval(substr($protokol_num, -7,2)).'-'.intval(substr($protokol_num, -5));

        $date =  Carbon::createFromFormat('Y-m-d H:i:s', $data['protokol_dt']);
        $data['protokol_dt'] = $date->format('d.m.Y');
//dd($data);
        $data[] = ['title' => 'Poverkadoma.ru'];
        $pdf = PDF::loadView('protokolPDF', $data)->setPaper('a4', 'landscape');

        return $pdf->download("svid ".$data['protokol_num'].".pdf");
    }

}
