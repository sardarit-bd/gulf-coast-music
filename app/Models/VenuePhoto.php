<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenuePhoto extends Model
{
    protected $table = 'venue_photos';

    protected $fillable = [
        'venue_id',
        'path',
        'alt',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
