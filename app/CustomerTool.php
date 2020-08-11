<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerTool extends Model
{
    protected $fillable = ['customer_id', 'typeNum', 'manufactureNum'];
}
