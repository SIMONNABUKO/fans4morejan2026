<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['permission_set_id', 'type', 'value'];

    public function permissionSet()
    {
        return $this->belongsTo(PermissionSet::class);
    }
}

