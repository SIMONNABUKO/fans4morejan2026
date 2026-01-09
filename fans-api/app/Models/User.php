<?php

namespace App\Models;

use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\CoverPhotoService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Log;
use App\Models\BookmarkAlbum;
use App\Models\Bookmark;
use App\Services\MediaStorageService;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;


    // protected static function boot()
    // {
    //     parent::boot();

    //     static::observe(UserObserver::class);
    // }
    protected $fillable = [
        'name',
        'email',
        'username',
        'handle',
        'password',
        'role',
        'avatar',
        'is_online',
        'is_suspended',
        'is_banned',
        'last_seen_at',
        'google_id',
        'bio',
        'bio_color',
        'bio_font',
        'facebook_id',
        'terms_accepted',
        'can_be_followed',
        'media_watermark',
        'location',
        'ip_address',
        'country_code',
        'country_name',
        'region_name',
        'city_name',
        'latitude',
        'longitude',
        'location_updated_at',
        'cover_photo',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'api_token',
        'display_name',
        'has_2fa',
        'referral_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'terms_accepted' => 'boolean',
        'can_be_followed' => 'boolean',
        'is_online' => 'boolean',
        'is_suspended' => 'boolean',
        'is_banned' => 'boolean',
        'has_2fa' => 'boolean',
    ];

    protected $appends = [
        'total_likes_received',
        'total_video_uploads',
        'total_image_uploads',
        'followers_count'
    ];

    public function getAvatarAttribute($value)
    {
        return app(MediaStorageService::class)->url($value);
    }

    public function getProfilePhotoAttribute($value)
    {
        return app(MediaStorageService::class)->url($value);
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = strtolower($value);
        $this->attributes['handle'] = '@' . strtolower($value);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function bookmarkAlbums(): HasMany
    {
        return $this->hasMany(BookmarkAlbum::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'followed_id', 'follower_id')
                    ->select('users.*')
                    ->withTimestamps()
                    ->withTrashed();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'followed_id')
                    ->select('users.*')
                    ->withTimestamps()
                    ->withTrashed();
    }

    public function getFollowersCountAttribute()
    {
        return $this->followers()->count();
    }

    public function getTotalLikesReceivedAttribute()
    {
        return $this->posts()->withCount('likes')->get()->sum('likes_count')
            + $this->media()->withCount('likes')->get()->sum('likes_count');
    }

    public function getTotalVideoUploadsAttribute()
    {
        return $this->media()->where('type', 'video')->count();
    }

    public function getTotalImageUploadsAttribute()
    {
        return $this->media()->where('type', 'image')->count();
    }

    public function followedAndSubscribedUsers()
    {
        $followedUsers = $this->following()->pluck('users.id');
        $subscribedUsers = $this->subscriptions()->pluck('creator_id');

        // Merge followed and subscribed users, ensure uniqueness, and exclude the current user
        return $followedUsers->merge($subscribedUsers)
            ->unique()
            ->filter(function ($userId) {
                return $userId !== $this->id;
            });
    }

    public function followedUsers()
    {
        return $this->following()->pluck('users.id')
            ->filter(function ($userId) {
                return $userId !== $this->id;
            });
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'subscriber_id');
    }

    public function isSubscribedTo(User $creator)
    {
        return $this->subscriptions()->where('creator_id', $creator->id)->exists();
    }

    public function isFollowing(User $user)
    {
        return $this->following()->where('followed_id', $user->id)->exists();
    }


    public function settings()
    {
        return $this->hasMany(UserSetting::class);
    }

    public function getSetting($category, $key, $default = null)
    {
        $setting = $this->settings()
            ->whereHas('category', function ($query) use ($category) {
                $query->where('name', $category);
            })
            ->where('key', $key)
            ->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->value;

        // Convert boolean strings back to actual booleans
        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }

        // Attempt to JSON decode – if valid JSON, return decoded value
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    public function setSetting($category, $key, $value)
    {
        $categoryModel = SettingCategory::firstOrCreate(['name' => $category]);

        // Normalize booleans to string representation
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        // Encode arrays/objects as JSON for storage
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        $setting = $this->settings()->firstOrNew([
            'setting_category_id' => $categoryModel->id,
            'key' => $key,
        ]);
        $setting->value = $value;
        $setting->save();

        // Log removed for production

        return $setting;
    }

    public function getCoverPhotoAttribute($value)
    {
        if (!$value) {
            $value = app(CoverPhotoService::class)->generateAndStoreCoverPhoto($this);
        }
        return app(MediaStorageService::class)->url($value);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function walletHistories()
    {
        return $this->hasMany(WalletHistory::class);
    }

    public function payoutMethods()
    {
        return $this->hasMany(PayoutMethod::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function tiers()
    {
        return $this->hasMany(Tier::class);
    }



    public function lists(): HasMany
    {

        return $this->hasMany(Lists::class);
    }

    public function listTypes(): BelongsToMany
    {
        return $this->belongsToMany(ListType::class, 'user_lists');
    }




    public function allLists()
    {
        $defaultLists = ListType::where('is_default', true)
            ->leftJoin('user_lists', function ($join) {
                $join->on('list_types.id', '=', 'user_lists.list_type_id')
                    ->where('user_lists.user_id', '=', $this->id);
            })
            ->select([
                'list_types.id',
                'list_types.name',
                'list_types.description',
                DB::raw('1 as is_default'),
                DB::raw('COALESCE(user_lists.list_id, list_types.id) as list_id'),
                DB::raw('COALESCE(user_lists.created_at, NOW()) as created_at'),
                DB::raw('COALESCE(user_lists.updated_at, NOW()) as updated_at'),
                DB::raw('(SELECT COUNT(*) FROM list_members WHERE list_members.list_type_id = list_types.id) as members_count'),
                DB::raw($this->id . ' as user_id'),
                'list_types.id as list_type_id',
                DB::raw($this->id . ' as pivot_user_id'),
                DB::raw('list_types.id as pivot_list_type_id')
            ]);

        $customLists = $this->hasMany(Lists::class)
            ->select([
                'lists.id',
                'lists.name',
                'lists.description',
                DB::raw('0 as is_default'),
                'lists.id as list_id',
                'lists.created_at',
                'lists.updated_at',
                DB::raw('(SELECT COUNT(*) FROM list_members WHERE list_members.list_id = lists.id) as members_count'),
                'lists.user_id',
                DB::raw('NULL as list_type_id'),
                DB::raw('NULL as pivot_user_id'),
                DB::raw('NULL as pivot_list_type_id')
            ]);

        return $defaultLists->union($customLists);
    }

    /**
     * Check if a user is in a specific list
     */
    public function isUserInList(User $user, string $listName): bool
    {
        Log::info('🔍 isUserInList called', [
            'auth_user_id' => $this->id,
            'profile_user_id' => $user->id,
            'list_name' => $listName
        ]);
        
        // Check if list is a default list (like Muted Accounts, Blocked Accounts)
        $listType = \App\Models\ListType::where('name', $listName)
            ->where('is_default', true)
            ->first();
        
        Log::info('🔍 ListType found', [
            'list_name' => $listName,
            'list_type_found' => $listType ? 'yes' : 'no',
            'list_type_id' => $listType?->id
        ]);
        
        if ($listType) {
            // Check if user is in this default list
            $exists = DB::table('list_members')
                ->where('list_type_id', $listType->id)
                ->where('user_id', $this->id)
                ->where('member_id', $user->id)
                ->exists();
            
            Log::info('🔍 Database check result', [
                'list_type_id' => $listType->id,
                'user_id' => $this->id,
                'member_id' => $user->id,
                'exists' => $exists
            ]);
            
            return $exists;
        }
        
        return false;
    }

    /**
     * Get all purchases made by the user.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Check if the user has purchased a specific item.
     */
    public function hasPurchased($purchasable)
    {
        return $this->purchases()
            ->where('purchasable_id', $purchasable->id)
            ->where('purchasable_type', get_class($purchasable))
            ->exists();
    }

    /**
     * Get the referrals created by this user.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Get the referral where this user was referred.
     */
    public function referredBy(): HasMany
    {
        return $this->hasMany(Referral::class, 'referred_id');
    }

    /**
     * Get the referral earnings for this user.
     */
    public function referralEarnings(): HasManyThrough
    {
        return $this->hasManyThrough(ReferralEarning::class, Referral::class, 'referrer_id');
    }

    /**
     * Check if the user has a referral code.
     */
    public function hasReferralCode(): bool
    {
        return !empty($this->referral_code);
    }

    /**
     * Generate a unique referral code for the user.
     */
    public function generateReferralCode(): string
    {
        if ($this->hasReferralCode()) {
            return $this->referral_code;
        }

        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (static::where('referral_code', $code)->exists());

        $this->update(['referral_code' => $code]);

        return $code;
    }

    public function referralCodeHistory()
    {
        return $this->hasMany(ReferralCodeHistory::class);
    }

    public function trackingLinks()
    {
        return $this->hasMany(TrackingLink::class, 'creator_id');
    }

    public function createdDiscounts()
    {
        return $this->hasMany(SubscriptionDiscount::class, 'created_by');
    }

    /**
     * Get the blocked locations for this user.
     */
    public function blockedLocations()
    {
        return $this->hasMany(BlockedLocation::class);
    }
}
