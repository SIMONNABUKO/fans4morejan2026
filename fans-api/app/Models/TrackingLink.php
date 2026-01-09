<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrackingLink extends Model
{
    protected $fillable = [
        'creator_id',
        'name',
        'slug',
        'description',
        'is_active',
        'full_url'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id')
            ->select(['id', 'username', 'display_name', 'avatar']);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TrackingLinkEvent::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(TrackingLinkAction::class);
    }

    public function clicks()
    {
        return $this->events()->where('event_type', 'click');
    }

    public function subscriptions()
    {
        return $this->events()->where('event_type', 'subscription');
    }

    public function purchases()
    {
        return $this->events()->where('event_type', 'purchase');
    }
} 