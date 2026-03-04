<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductSerialNumber extends Model
{
    protected $guarded = ['id'];

    protected $dates = ['sold_at'];
}
