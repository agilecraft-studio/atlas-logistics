<?php

/*
@copyright

Fleet Manager v7.0

Copyright (C) 2017-2023 Atlas Logistics <https://app.atlaslogistics.co.mw> All rights reserved.
Design and developed by Atlas Logistics <https://app.atlaslogistics.co.mw>

*/

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverPayments extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'driver_payments';

    protected $fillable = ['driver_id', 'user_id', 'amount', 'notes'];

    public function driver()
    {
        return $this->belongsTo('App\Model\User','driver_id')->withTrashed();
    }
}
