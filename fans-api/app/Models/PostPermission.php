<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostPermission extends Model
{
    protected $fillable = ['post_id', 'type', 'value'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

