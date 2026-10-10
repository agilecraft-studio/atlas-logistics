<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trailer extends Model
{
    use SoftDeletes;
    protected $table = 'trailers';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    public function getLabelAttribute() { return $this->registration_number . ($this->trailer_type ? ' (' . $this->trailer_type . ')' : ''); }
}
