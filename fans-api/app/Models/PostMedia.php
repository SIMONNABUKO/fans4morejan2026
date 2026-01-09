<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    protected $fillable = ['post_id', 'type', 'url'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function previews()
    {
        return $this->hasMany(PostMediaPreview::class);
    }
}

