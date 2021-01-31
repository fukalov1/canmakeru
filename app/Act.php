<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Act extends Model
{
    protected $fillable = ['number_act','name','customer_id','date','miowner','pin','address','lat','lng', 'type'];
    protected $appends = ['date1'];

    protected $casts = [
        'date' => 'datetime:Y-m-d'
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function meters()
    {
        return $this->hasMany(Protokol::class, 'act_id', 'id');
    }

    public function getNmbrActAttribute()
    {
//        $year = $this->created_at ? date('yy', $this->created_at) : '20';
        $year = 20;
        return $this->customer->partner_code."-".$year."-".$this->id;
    }

    protected function getDate1Attribute()
    {
        return date('H:i:s', strtotime($this->date));
    }

}
