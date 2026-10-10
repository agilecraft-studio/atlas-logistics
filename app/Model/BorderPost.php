<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BorderPost extends Model
{
    protected $table = 'border_posts';
    protected $guarded = ['id'];

    public function country() { return $this->belongsTo(Country::class, 'country_id'); }
    public function neighbour() { return $this->belongsTo(Country::class, 'neighbour_country_id'); }
}
