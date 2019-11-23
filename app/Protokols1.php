<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Protokols1 extends Model
{
    protected $table = 'protokols1';
    protected $fillable = ['protokol_num', 'pin', 'protokol_photo', 'protokol_photo1', 'meter_photo', 'customer_id', 'updated_dt', 'lat', 'lng', 'protokol_dt'];
}
