<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currencies';
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean'];
}
