<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorApplicationHistory extends Model
{
    use HasFactory;

    protected $table = 'creator_application_history';

    protected $fillable = [
        'application_id',
        'admin_id',
        'status',
        'feedback',
        'processed_at'
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the application that this history belongs to.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(CreatorApplication::class, 'application_id');
    }

    /**
     * Get the admin who made this change.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
} 