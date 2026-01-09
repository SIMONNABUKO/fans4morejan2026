<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedLocation extends Model
{
    protected $fillable = [
        'user_id', 'country_code', 'country_name', 'location_type', 'region_name', 'city_name', 
        'latitude', 'longitude', 'display_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

