<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'provider',
        'account_number',
        'account_name',
        'is_default',
        'details',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function getLastFourDigits()
    {
        if (strlen($this->account_number) > 4) {
            return substr($this->account_number, -4);
        }
        return $this->account_number;
    }

    public function getDisplayName()
    {
        return $this->provider . ' ending in ' . $this->getLastFourDigits();
    }
}