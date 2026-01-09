<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use App\Models\Media;
use App\Models\Permission;
use App\Models\PermissionSet;
use App\Models\Subscription;
use App\Models\PostPurchase;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PermissionService
{
  /**
   * Create permissions for a model (Post or Message)
   *
   * @param Model $model
   * @param array $permissions
   * @return void
   */
  public function createPermissions(Model $model, array $permissions)
  {
      foreach ($permissions as $permissionGroup) {
          $permissionSet = $model->permissionSets()->create();
          
          foreach ($permissionGroup as $permission) {
              $permissionSet->permissions()->create([
                  'type' => $permission['type'],
                  'value' => $this->formatPermissionValue($permission),
              ]);
          }
      }
  }

  /**
   * Update permissions for a model
   *
   * @param Model $model
   * @param array $permissions
   * @return void
   */
  public function updatePermissions(Model $model, array $permissions)
  {
      // Delete existing permissions
      $model->permissionSets()->delete();

      // Create new permissions
      $this->createPermissions($model, $permissions);
  }

    /**
     * Check if a user has permission to access content (message or post)
     *
     * @param mixed $content Message or Post model
     * @param User $user
     * @return bool
     */
    public function checkPermission($content, User $user): bool
    {


        try {
            $contentType = get_class($content);
            $isMessage = $contentType === Message::class;
            $isPost = $contentType === 'App\Models\Post';
        

        
            // If the user is the owner, they always have permission
            $ownerId = $isMessage ? $content->sender_id : $content->user_id;
            if ($ownerId === $user->id) {

                return true;
            }
        
            // If the content has no media, the viewer always has permission
            if ($content->media->isEmpty()) {

                return true;
            }
        
            // If the content has no permission sets, the viewer has permission
            if ($content->permissionSets->isEmpty()) {

                return true;
            }
        
            // Check each permission set
            foreach ($content->permissionSets as $permissionSet) {
                $allPermissionsMet = true;
            
                // Check if any permission group is satisfied
                foreach ($permissionSet->permissions as $permission) {
                    $permissionMet = $this->checkSinglePermission($permission, $user, $ownerId);
                
                    if (!$permissionMet) {
                        $allPermissionsMet = false;
                        break;
                    }
                }
            
                if ($allPermissionsMet) {

                    return true;
                }
            }
        

        
            return false;
        } catch (\Exception $e) {
            Log::error('❌ Error in checkPermission: ' . $e->getMessage(), [
                'content_id' => $content->id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
        
            // Default to no permission on error
            return false;
        }
    }
    
    /**
     * Check if a single permission is met
     *
     * @param Permission $permission
     * @param User $user
     * @param int $ownerId
     * @return bool
     */
    protected function checkSinglePermission(Permission $permission, User $user, int $ownerId): bool
    {
        try {

            
            switch ($permission->type) {
                case 'subscribed_all_tiers':
                    // Check if user is subscribed to any tier of the owner
                    $now = Carbon::now();
                    
                    // First check for active subscriptions
                    $isSubscribed = Subscription::where('subscriber_id', $user->id)
                        ->where('creator_id', $ownerId)
                        ->where('status', Subscription::ACTIVE_STATUS)
                        ->where('start_date', '<=', $now)
                        ->where('end_date', '>=', $now)
                        ->exists();
                    
                    // If not active, check for canceled subscriptions that haven't expired yet
                    // Users should maintain access until their paid period ends
                    if (!$isSubscribed) {
                        $isSubscribed = Subscription::where('subscriber_id', $user->id)
                            ->where('creator_id', $ownerId)
                            ->where('status', Subscription::CANCELED_STATUS)
                            ->where('start_date', '<=', $now)
                            ->where('end_date', '>=', $now)
                            ->exists();
                    }
                    
                    // Debug: Check all subscriptions for this user-creator pair
                    $allSubscriptions = Subscription::where('subscriber_id', $user->id)
                        ->where('creator_id', $ownerId)
                        ->get();
                    
                    // Log removed for production
                    

                    
                    return $isSubscribed;
                    
                    case 'add_price':
                        // Check if user has paid for this content
                        $contentId = $permission->permissionSet->permissionable_id;
                        $contentType = $permission->permissionSet->permissionable_type;
                        // Standardize to fully qualified class name
                        if ($contentType === 'post') $contentType = \App\Models\Post::class;
                        if ($contentType === 'message') $contentType = \App\Models\Message::class;
                        // First check if there's a purchase record
                        $hasPaid = \App\Models\Purchase::where('user_id', $user->id)
                            ->where('purchasable_id', $contentId)
                            ->where('purchasable_type', $contentType)
                            ->exists();
                        // If no purchase record, check for approved transaction
                        if (!$hasPaid) {
                            $transaction = \App\Models\Transaction::where('sender_id', $user->id)
                                ->where('purchasable_id', $contentId)
                                ->where('purchasable_type', $contentType)
                                ->where('status', \App\Models\Transaction::APPROVED_STATUS)
                                ->first();
                            if ($transaction) {
                                // Create a purchase record
                                $purchase = \App\Models\Purchase::create([
                                    'user_id' => $user->id,
                                    'purchasable_id' => $contentId,
                                    'purchasable_type' => $contentType,
                                    'price' => $transaction->amount,
                                    'transaction_id' => $transaction->id
                                ]);
                                // Check if there's a tracking link ID in the transaction's additional data
                                if (isset($transaction->additional_data['tracking_link_id'])) {
                                    $trackingLinkId = $transaction->additional_data['tracking_link_id'];
                                    $trackingLink = \App\Models\TrackingLink::find($trackingLinkId);
                                    if ($trackingLink) {
                                        app(\App\Services\TrackingLinkActionService::class)->trackAction(
                                            $trackingLink,
                                            'purchase',
                                            $user->id,
                                            [
                                                'transaction_id' => $transaction->id,
                                                'purchase_id' => $purchase->id,
                                                'amount' => $transaction->amount,
                                                'purchasable_id' => $contentId,
                                                'purchasable_type' => $contentType
                                            ]
                                        );
                                    }
                                }
                                $hasPaid = true;
                            }
                        }
                        // Log removed for production
                        return $hasPaid;
                        
                    
                case 'following':
                    // Check if user is following the owner
                    $isFollowing = $user->isFollowing(User::find($ownerId));
                    
                    // Log removed for production
                    
                    return $isFollowing;
                    
                case 'limited_time':
                    // Check if the limited time access is still valid
                    $timeValue = json_decode($permission->value, true);
                    
                    if (!$timeValue || !isset($timeValue['end_date'])) {
                        return false;
                    }
                    
                    $endDate = Carbon::parse($timeValue['end_date']);
                    $isTimeValid = Carbon::now()->lt($endDate);
                    
                    // Log removed for production
                    
                    return $isTimeValid;
                    
                default:
                    Log::warning('⚠️ Unknown permission type: ' . $permission->type);
                    return false;
            }
        } catch (\Exception $e) {
            Log::error('❌ Error in checkSinglePermission: ' . $e->getMessage(), [
                'permission_id' => $permission->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Default to no permission on error
            return false;
        }
    }
    
    /**
     * Get all required permissions that have not been met
     *
     * @param mixed $content Message or Post model
     * @param User $user
     * @return array
     */
    public function getRequiredPermissions($content, User $user): array
    {
        try {
            $contentType = get_class($content);
            $isMessage = $contentType === Message::class;


        
            $requiredPermissions = [];
        
            // If the user is the owner, they don't need any permissions
            $ownerId = $isMessage ? $content->sender_id : $content->user_id;
            if ($ownerId === $user->id) {
                return $requiredPermissions;
            }
        
            // If the content has no media or no permission sets, no permissions are required
            if ($content->media->isEmpty() || $content->permissionSets->isEmpty()) {
                return $requiredPermissions;
            }
        
            // Group permissions by permission set
            foreach ($content->permissionSets as $permissionSet) {
                $permissionGroup = [];
            
                foreach ($permissionSet->permissions as $permission) {
                    $permissionMet = $this->checkSinglePermission($permission, $user, $ownerId);
                
                    // Add the permission to the group with its status
                    $permissionGroup[] = [
                        'type' => $permission->type,
                        'value' => $permission->value,
                        'is_met' => $permissionMet
                    ];
                }
            
                // Only add the group if it has permissions
                if (!empty($permissionGroup)) {
                    $requiredPermissions[] = $permissionGroup;
                }
            }
        

        
            return $requiredPermissions;
        } catch (\Exception $e) {
            Log::error('❌ Error in getRequiredPermissions: ' . $e->getMessage(), [
                'content_id' => $content->id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
        
            // Return empty array on error
            return [];
        }
    }

  /**
   * Format permission value based on type
   *
   * @param array $permission
   * @return mixed
   */
  protected function formatPermissionValue($permission)
  {
      if ($permission['type'] === 'limited_time') {
          return json_encode($permission['value']);
      }

      return $permission['value'];
  }
}