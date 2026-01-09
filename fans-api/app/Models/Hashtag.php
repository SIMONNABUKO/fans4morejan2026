<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Hashtag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'posts_count',
        'followers_count',
        'is_trending',
    ];

    protected $casts = [
        'is_trending' => 'boolean',
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_hashtags')
            ->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'hashtag_followers')
            ->withTimestamps();
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function getDisplayNameAttribute()
    {
        return '#' . $this->name;
    }

    public function incrementPostsCount()
    {
        $this->increment('posts_count');
    }

    public function decrementPostsCount()
    {
        $this->decrement('posts_count');
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true);
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('posts_count', 'desc')->limit($limit);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }
} 