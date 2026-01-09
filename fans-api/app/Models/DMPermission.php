<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DMPermission extends Model
{
    protected $fillable = ['user_id'];

    public function permissionSets()
    {
        return $this->morphMany(PermissionSet::class, 'permissionable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}