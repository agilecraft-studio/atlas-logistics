<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripExpense extends Model
{
    use SoftDeletes;

    protected $table = 'trip_expenses';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    protected $casts = ['expense_date' => 'date', 'approved_at' => 'datetime'];

    public function trip()     { return $this->belongsTo(Trip::class); }
    public function category() { return $this->belongsTo(TripExpenseCategory::class, 'category_id'); }
    public function country()  { return $this->belongsTo(Country::class, 'country_id'); }
}
