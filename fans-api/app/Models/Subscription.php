<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
  protected $fillable = [
    'subscriber_id', 
    'creator_id', 
    'tier_id',
    'start_date', 
    'end_date', 
    'price',
    'status',
    'amount',
    'duration',
    'is_vip',
    'is_muted',
    'transaction_id'
  ];

  protected $dates = ['start_date', 'end_date', 'cancel_date', 'renew_date'];

  protected $casts = [
    'is_vip' => 'boolean',
    'is_muted' => 'boolean',
    'amount' => 'float',
    'duration' => 'integer'
  ];

  public const ACTIVE_STATUS = 'completed';
  public const SUSPENDED_STATUS = 'suspended';
  public const CANCELED_STATUS = 'canceled';
  public const EXPIRED_STATUS = 'expired';
  public const PENDING_STATUS = 'pending';

  // Renewal failed
  public const FAILED_STATUS = 'failed';

  public function subscriber()
  {
      return $this->belongsTo(User::class, 'subscriber_id');
  }

  public function creator()
  {
      return $this->belongsTo(User::class, 'creator_id');
  }
  
  // Add relationship to Tier model
  public function tier()
  {
      return $this->belongsTo(Tier::class, 'tier_id');
  }

  // Add relationship to Transaction model
  public function transaction()
  {
      return $this->belongsTo(Transaction::class);
  }
}

