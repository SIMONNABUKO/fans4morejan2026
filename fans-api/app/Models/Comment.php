<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\MediaStorageService;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'post_id', 'content', 'media_url', 'scheduled_for', 'delete_at'];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'delete_at' => 'datetime',
    ];

    public function getMediaUrlAttribute($value)
    {
        return app(MediaStorageService::class)->url($value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
