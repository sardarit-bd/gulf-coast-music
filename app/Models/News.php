<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'news';
    protected $fillable = [
        'journalist_id',
        'title',
        'description',
        'news_date',
        'location',
        'credit',
        'status',
        'published_at',
    ];

    public function journalist()
    {
        return $this->belongsTo(Journalist::class);
    }
}
