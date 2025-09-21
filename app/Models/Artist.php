<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'genre',
        'image',
        'cover_photo',
        'bio',
        'city',
    ];

    // 🔗 Relation: Artist belongs to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔗 Relation: Artist has many photos
    public function photos()
    {
        return $this->hasMany(ArtistPhoto::class);
    }

    // 🔗 Relation: Artist has many songs
    public function songs()
    {
        return $this->hasMany(ArtistSong::class);
    }
}
