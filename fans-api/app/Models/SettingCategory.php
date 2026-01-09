<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function userSettings()
    {
        return $this->hasMany(UserSetting::class);
    }
}

