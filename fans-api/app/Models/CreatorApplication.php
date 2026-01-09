<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreatorApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'birthday',
        'address',
        'city',
        'user_id',
        'country',
        'state',
        'zip_code',
        'document_type',
        'front_id',
        'back_id',
        'holding_id',
        'status',
        'feedback',
        'processed_at'
    ];

    protected $casts = [
        'birthday' => 'date',
        'processed_at' => 'datetime'
    ];

    /**
     * Get the user that owns the application.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the history records for this application.
     */
    public function history(): HasMany
    {
        return $this->hasMany(CreatorApplicationHistory::class, 'application_id')->orderBy('processed_at', 'desc');
    }
}