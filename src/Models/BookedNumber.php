<?php

namespace tanyudii\YinNumber\Models;

use Illuminate\Database\Eloquent\Model;

class BookedNumber extends Model
{
    protected $fillable = ['table', 'number'];
}