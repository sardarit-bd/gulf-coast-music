<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $table = 'venues';

    protected $fillable = [
        'user_id',
        'name',
        'city',
        'address',
        'state',
        'zip',
        'phone',
        'capacity',
        'biography',
        'open_hours',
        'open_days',
        'color',
    ];

    // 🔗 Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(VenuePhoto::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city', 'name');
    }
}
