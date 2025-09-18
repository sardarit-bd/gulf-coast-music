<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    // 🔗 Relation: Many Artists can have many Genres
    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'artist_genre');
    }
}
