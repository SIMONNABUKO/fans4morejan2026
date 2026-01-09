<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AutomatedMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trigger',
        'content',
        'sent_delay',
        'cooldown',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean', 
        'sent_delay' => 'integer',
        'cooldown' => 'integer'
    ];

    /**
     * Get the media associated with the automated message.
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get the user that owns the automated message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the permission sets for the automated message.
     */
    public function permissionSets()
    {
        return $this->morphMany(PermissionSet::class, 'permissionable');
    }

    /**
     * Check if a user has permission to view this message
     */
    public function hasPermission($user, $requiredPermission = null)
    {
        $permissionService = app(\App\Services\PermissionService::class);
        return $permissionService->checkPermission($this, $user, $requiredPermission);
    }
}