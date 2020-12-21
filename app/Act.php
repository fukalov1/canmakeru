<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Act extends Model
{
    protected $fillable = ['number_act','customer_id','date','pin','address','lat','lng', 'type'];
    protected $appends = ['nmbr_act'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getNmbrActAttribute()
    {
//        $year = $this->created_at ? date('yy', $this->created_at) : '20';
        $year = 20;
        return $this->customer->partner_code."-".$year."-".$this->id;
    }


}
