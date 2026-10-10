<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TripCheckpoint extends Model
{
    protected $table = 'trip_checkpoints';
    protected $guarded = ['id'];
    protected $casts = ['arrived_at' => 'datetime', 'departed_at' => 'datetime'];

    public function trip()       { return $this->belongsTo(Trip::class); }
    public function borderPost() { return $this->belongsTo(BorderPost::class, 'border_post_id'); }

    /** Hours spent at this checkpoint (null while still there). */
    public function getHoursAttribute()
    {
        return $this->departed_at ? round($this->arrived_at->diffInMinutes($this->departed_at) / 60, 1) : null;
    }
}
