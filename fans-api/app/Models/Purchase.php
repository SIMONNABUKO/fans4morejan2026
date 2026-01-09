<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'user_id', 
        'purchasable_id', 
        'purchasable_type', 
        'permission_set_id', 
        'price', 
        'transaction_id'
    ];

    /**
     * Get the user who made the purchase.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the purchased item (post or message).
     */
    public function purchasable()
    {
        return $this->morphTo();
    }

    /**
     * Get the permission set associated with this purchase.
     */
    public function permissionSet()
    {
        return $this->belongsTo(PermissionSet::class);
    }
    
    /**
     * Get the transaction associated with this purchase.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}

