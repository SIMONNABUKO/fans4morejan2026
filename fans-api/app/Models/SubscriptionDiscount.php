<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'tier_id',
        'label',
        'max_uses',
        'discounted_price',
        'discount_percent',
        'amount_off',
        'start_date',
        'end_date',
        'duration_days',
        'exclude_previous_claimers',
        'created_by',
    ];

    public function tier()
    {
        return $this->belongsTo(Tier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
} 