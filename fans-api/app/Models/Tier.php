<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($tier) {
            \Illuminate\Support\Facades\Log::info('Tier model retrieved', [
                'tier_id' => $tier->id,
                'tier_data' => $tier->toArray()
            ]);
        });

        static::deleting(function ($tier) {
            \Illuminate\Support\Facades\Log::info('Tier model deleting event triggered', [
                'tier_id' => $tier->id,
                'tier_data' => $tier->toArray()
            ]);
        });

        static::deleted(function ($tier) {
            \Illuminate\Support\Facades\Log::info('Tier model deleted event triggered', [
                'tier_id' => $tier->id,
                'tier_data' => $tier->toArray()
            ]);
            
            // Clear any related caches if needed
            \Illuminate\Support\Facades\Cache::tags(['tiers'])->flush();
        });
    }

    protected $fillable = [
        'user_id',
        'title',
        'color_code',
        'subscription_benefits',
        'base_price',
        'two_month_price',
        'three_month_price',
        'six_month_price',
        'two_month_discount',
        'three_month_discount',
        'six_month_discount',
        'active_plans',
        'subscriptions_enabled',
        'description',
        'max_subscribers',
        'max_subscribers_enabled',
        'is_enabled'
    ];

    protected $casts = [
        'subscription_benefits' => 'array',
        'active_plans' => 'array',
        'base_price' => 'decimal:2',
        'two_month_price' => 'decimal:2',
        'three_month_price' => 'decimal:2',
        'six_month_price' => 'decimal:2',
        'two_month_discount' => 'integer',
        'three_month_discount' => 'integer',
        'six_month_discount' => 'integer',
        'subscriptions_enabled' => 'boolean',
        'max_subscribers_enabled' => 'boolean',
        'is_enabled' => 'boolean'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'subscriptions', 'tier_id', 'subscriber_id')->withTimestamps();
    }

    public function activeSubscribers()
    {
        return $this->belongsToMany(User::class, 'subscriptions', 'tier_id', 'subscriber_id')
            ->wherePivot('status', 'active')
            ->whereNull('subscriptions.cancel_date')
            ->whereDate('subscriptions.end_date', '>', now())
            ->withTimestamps();
    }

    public function getActivePlansAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setActivePlansAttribute($value)
    {
        $this->attributes['active_plans'] = json_encode($value);
    }

    public function getPriceForDuration($duration)
    {
        switch ($duration) {
            case 1:
                return $this->base_price;
            case 2:
                return $this->two_month_price ?? ($this->base_price * 2 * (1 - $this->two_month_discount / 100));
            case 3:
                return $this->three_month_price ?? ($this->base_price * 3 * (1 - $this->three_month_discount / 100));
            case 6:
                return $this->six_month_price ?? ($this->base_price * 6 * (1 - $this->six_month_discount / 100));
            default:
                return null;
        }
    }

    public function discounts()
    {
        return $this->hasMany(SubscriptionDiscount::class);
    }
}

