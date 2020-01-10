<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Protokol;
use Illuminate\Http\Request;
use PDF;

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

    public function getDataProtokols()
    {
        $customer_id = 0;
        if (request()->customer_id)
            $customer_id = request()->customer_id;
        return $this->customer->getProtokols($customer_id);
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

    public function getProfile()
    {
        return json_encode($this->customer->find(auth()->guard('customer')->user()->id));
    }

    public function getPDF($id)
    {

        PDF::setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif', 'setPaper'=> 'landscape']);

        $data = Protokol::find($id)->toArray();
//dd($data);
        $data[] = ['title' => 'Poverkadoma.ru'];
        $pdf = PDF::loadView('protokolPDF', $data);

        return $pdf->download("protokol$id.pdf");
    }

}
