<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TripExpenseCategory extends Model
{
    protected $table = 'trip_expense_categories';
    protected $guarded = ['id'];
    protected $casts = ['requires_receipt' => 'boolean', 'is_active' => 'boolean'];
}
