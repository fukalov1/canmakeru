<?php

namespace App;
use App\Customer;
use Illuminate\Database\Eloquent\Model;

class SlaveCustomer extends Model
{

    protected $fillable = ['id', 'customer_id', 'slave_id'];

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function slave() {
        return $this->belongsTo(Customer::class, 'slave_id');
    }

}
