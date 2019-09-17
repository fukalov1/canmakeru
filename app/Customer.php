<?php

namespace App;

use App\Protokol;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function protokols() {
	return $this->hasMany(Protokol::class);
    }

}
