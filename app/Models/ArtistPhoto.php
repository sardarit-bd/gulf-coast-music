<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtistPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'photo',
    ];

    // 🔗 Relation: Photo belongs to Artist
    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
}
