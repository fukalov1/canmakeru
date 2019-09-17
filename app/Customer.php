<?php

namespace App;

use App\Protokols;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function protokols() {
	return $this->BelongTo(Protokols::class);
    }

}
