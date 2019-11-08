<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;

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

    public function getDataProtokols()
    {
        return $this->customer->getProtokols();
    }

    public function getDataStatistic()
    {
        return $this->customer->getDataChart();
    }

    public function getProfile()
    {
        return json_encode($this->customer->find(auth()->guard('customer')->user()->id));
    }

}
