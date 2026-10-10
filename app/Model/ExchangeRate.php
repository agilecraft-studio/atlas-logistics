<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';
    protected $guarded = ['id'];
    protected $casts = ['effective_date' => 'date', 'rate' => 'float'];
}
