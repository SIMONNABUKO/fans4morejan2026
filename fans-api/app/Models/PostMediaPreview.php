<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMediaPreview extends Model
{
    protected $fillable = ['post_media_id', 'url'];

    public function postMedia()
    {
        return $this->belongsTo(PostMedia::class);
    }

    public function media()
    {
        return $this->belongsTo(PostMedia::class, 'post_media_id');
    }
}

