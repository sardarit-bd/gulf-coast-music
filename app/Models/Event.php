<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'venue_id',
        'artist',
        'date',
        'time',
        'city',
        'color'
    ];

    // Relations
    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function photos()
    {
        return $this->hasMany(EventPhoto::class);
    }
}
