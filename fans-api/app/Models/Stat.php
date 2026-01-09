<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    use HasFactory;

    protected $fillable = [
        'statable_id',
        'statable_type',
        'total_likes',
        'total_views',
        'total_bookmarks',
        'total_comments',
        'total_tips',
        'total_tip_amount',
    ];

    protected $casts = [
        'total_tip_amount' => 'decimal:2',
    ];

    public function statable()
    {
        return $this->morphTo();
    }
}

