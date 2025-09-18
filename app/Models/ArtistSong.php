<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtistSong extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'title',
        'mp3_url',
    ];

    // 🔗 Relation: Song belongs to Artist
    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
}
