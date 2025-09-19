<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journalist extends Model
{
    protected $table = 'journalists';
    protected $fillable = ['user_id', 'name', 'email', 'phone', 'image'];

    // --- IGNORE ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }
}
