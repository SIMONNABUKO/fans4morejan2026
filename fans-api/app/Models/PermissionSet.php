<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionSet extends Model
{
    protected $fillable = ['permissionable_id', 'permissionable_type'];

    public function permissionable()
    {
        return $this->morphTo();
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
}

