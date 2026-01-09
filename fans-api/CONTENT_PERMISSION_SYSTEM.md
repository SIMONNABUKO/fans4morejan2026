# Content & Permission System - Comprehensive Analysis

## 🎯 Overview

This document provides a deep dive into how content creation, loading, permissions, and unlocking work in the Fans platform. This is the **CORE** monetization and content gating system.

---

## 📊 System Architecture

### Key Components

```
Content Creation → Permission Assignment → Content Storage
                                         ↓
Content Loading → Permission Checking → Visibility Determination
                                         ↓
Unlocking → Payment Processing → Access Grant
```

---

## 1. CONTENT CREATION FLOW

### 1.1 Post Creation Process

**Location**: `app/Services/PostService.php::createPost()`

**Flow**:
```php
User Uploads Media + Sets Permissions
    ↓
PostService::createPost()
    ↓
1. Process Media Files (with watermarks)
2. Create Post Record
3. Store Media with Previews
4. Create Permission Sets
5. Tag Users (if applicable)
6. Sync Hashtags
7. Add to Vault Albums
```

**Key Code**:
```php
// app/Services/PostService.php
public function createPost(array $data, User $user): Post
{
    $processedData = $this->processMediaFiles($data);
    
    // Create post
    $post = $this->postRepository->create($processedData, $user);
    
    // Create permissions
    if (isset($data['permissions'])) {
        $this->permissionService->createPermissions($post, $data['permissions']);
    }
    
    // Process media and previews
    foreach ($post->media as $media) {
        $this->vaultService->addMediaToAlbum($media, 'All', $user);
        $this->vaultService->addMediaToAlbum($media, 'Posts', $user);
    }
    
    return $post;
}
```

### 1.2 Media Storage

**Structure**:
```
Media (full resolution)
    ├── id
    ├── url (watermarked)
    ├── type (image/video)
    └── previews[] (blurred/low-res versions)
        ├── id
        └── url
```

**Storage Process**:
1. User uploads media files
2. **Frontend**: Creates previews (blurred versions) using canvas/video processing
3. **Backend**: Stores both full media and previews
4. Media gets watermarked using `MediaService`
5. Previews stored in `media_previews` table

**Key Files**:
- `app/Services/MediaService.php` - Handles watermarking
- `app/Repositories/PostRepository.php` - Stores media
- `fans/src/stores/mediaUploadStore.js` - Frontend media handling

---

## 2. PERMISSION SYSTEM

### 2.1 Permission Architecture

**Database Structure**:
```
PermissionSet (permissionable_id, permissionable_type)
    └── Permissions[]
        ├── type (permission type)
        └── value (permission value)
```

**Relationship**: 
- `Post` → has many `PermissionSet`
- `PermissionSet` → has many `Permission`
- Polymorphic: Works for Posts, Messages, AutomatedMessages

### 2.2 Permission Types

#### A. `subscribed_all_tiers`
**Meaning**: User must be subscribed to ANY tier of the creator

**Check Logic**:
```php
$isSubscribed = Subscription::where('subscriber_id', $user->id)
    ->where('creator_id', $ownerId)
    ->where('status', Subscription::ACTIVE_STATUS)
    ->where('start_date', '<=', now())
    ->where('end_date', '>=', now())
    ->exists();
```

#### B. `add_price`
**Meaning**: User must pay to unlock content

**Check Logic**:
```php
// Check Purchase table
$hasPaid = Purchase::where('user_id', $user->id)
    ->where('purchasable_id', $contentId)
    ->where('purchasable_type', $contentType)
    ->exists();

// If no purchase, check Transaction table
if (!$hasPaid) {
    $transaction = Transaction::where('sender_id', $user->id)
        ->where('purchasable_id', $contentId)
        ->where('status', Transaction::APPROVED_STATUS)
        ->first();
    
    if ($transaction) {
        // Create purchase record
        Purchase::create([...]);
        $hasPaid = true;
    }
}
```

#### C. `following`
**Meaning**: User must be following the creator

**Check Logic**:
```php
$isFollowing = $user->isFollowing(User::find($ownerId));
```

#### D. `limited_time`
**Meaning**: Content available for limited time

**Check Logic**:
```php
$timeValue = json_decode($permission->value, true);
$endDate = Carbon::parse($timeValue['end_date']);
$isTimeValid = Carbon::now()->lt($endDate);
```

### 2.3 Permission Logic (Critical!)

**Location**: `app/Services/PermissionService.php::checkPermission()`

**Decision Tree**:
```
1. Is user the owner? → YES → ✅ ACCESS GRANTED
   ↓ NO
2. Does content have media? → NO → ✅ ACCESS GRANTED (text-only)
   ↓ YES
3. Does content have permission sets? → NO → ✅ ACCESS GRANTED (public media)
   ↓ YES
4. Loop through each PermissionSet:
   └─ Check if ALL permissions in set are met
      └─ If ANY set has all permissions met → ✅ ACCESS GRANTED
      └─ If NO set has all permissions met → ❌ ACCESS DENIED
```

**Critical Note**: This is an **OR** logic between sets, **AND** logic within sets.
- Multiple permission sets = "User meets ANY of these requirements"
- Multiple permissions in a set = "User must meet ALL requirements"

**Example**:
```php
Permission Sets:
Set 1: [subscribed_all_tiers]           // OR
Set 2: [add_price: 5.00]                // OR  
Set 3: [following, limited_time]        // OR (following AND limited_time)

User meets Set 1 → ✅ ACCESS
User meets Set 2 → ✅ ACCESS
User meets Set 3 → ✅ ACCESS (must meet BOTH following AND limited_time)
```

---

## 3. CONTENT LOADING FLOW

### 3.1 Feed Loading Process

**Location**: `app/Services/FeedService.php`

**Main Entry Points**:
1. `getFeedPosts()` - All posts from all creators
2. `getFeedPreviews()` - Video previews only
3. `getSubscribedFeedPosts()` - Subscribed creators only
4. `getListFeedPosts()` - Specific list

### 3.2 Feed Loading Steps

**Step 1: Determine Content Sources**
```php
// Get all creator IDs
$allCreatorUsers = User::where('role', 'creator')
    ->where('id', '!=', $user->id)
    ->pluck('id');
```

**Step 2: Location-Based Filtering**
```php
// Get blocked creator IDs based on user's location
$blockedCreatorIds = User::whereIn('id', $allCreatorUsers)
    ->whereHas('blockedLocations', function ($query) use ($userLocation) {
        // Check country, region, city levels
    })
    ->pluck('id');

// Filter out posts from blocked creators
$posts->setCollection(
    $posts->getCollection()->filter(function ($post) use ($blockedCreatorIds) {
        return !$blockedCreatorIds->contains($post->user_id);
    })
);
```

**Step 3: Permission Checking**
```php
// Add permission flags to each post
$posts->getCollection()->transform(function ($post) use ($user) {
    $post->user_has_permission = $this->permissionService->checkPermission($post, $user);
    $post->required_permissions = $this->permissionService->getRequiredPermissions($post, $user);
    return $post;
});
```

**Step 4: Preview Filtering** (for preview feed)
```php
// Only keep posts with video previews
$posts->setCollection(
    $posts->getCollection()->filter(function ($post) {
        foreach ($post->media as $media) {
            foreach ($media->previews as $preview) {
                if (preg_match('/\\.(mp4|webm|ogg)$/i', $preview->url)) {
                    return true;
                }
            }
        }
        return false;
    })
);

// Filter out already purchased posts
$posts->setCollection(
    $posts->getCollection()->filter(function ($post) use ($user) {
        $hasPermission = $this->permissionService->checkPermission($post, $user);
        return !$hasPermission; // Only show posts NOT purchased
    })
);
```

### 3.3 Response Format

**Backend sends**:
```json
{
  "posts": [
    {
      "id": 123,
      "content": "Post text",
      "user": {...},
      "media": [
        {
          "id": 456,
          "url": "https://.../image.jpg",
          "type": "image",
          "previews": [
            {"id": 789, "url": "https://.../preview.mp4"}
          ]
        }
      ],
      "permission_sets": [...],
      "user_has_permission": false,
      "required_permissions": [
        [
          {"type": "add_price", "value": "5.00", "is_met": false}
        ]
      ]
    }
  ]
}
```

---

## 4. FRONTEND CONTENT DISPLAY

### 4.1 Display Logic

**Location**: `fans/src/components/posts/Post.vue`

**Display Rules**:
```javascript
// If user has permission: Show full media
if (post.user_has_permission) {
    // Display: post.media.url (full resolution)
}

// If user doesn't have permission: Show preview
else {
    // Display: post.media.previews[0].url (blurred/low-res)
    // Show unlock button
}
```

### 4.2 Unlock Flow

**User clicks unlock**:
```javascript
1. Frontend shows unlock modal
2. User selects permission option
3. Frontend sends unlock request:
   POST /api/posts/{postId}/unlock
   Body: { permissions: [...] }
4. Backend processes payment
5. Backend creates Purchase record
6. Frontend reloads post
7. Backend checks permission again
8. user_has_permission = true
9. Frontend displays full media
```

---

## 5. TIPPING SYSTEM

### 5.1 Tip Types

**Two types of tips**:

#### A. Content Tips
- Tipping a post/media
- Creates `Tip` record linked to content
- Doesn't unlock content (separate from payment)

#### B. Message Tips
- Required to send messages to creators
- Creates unconsumed tip
- Gets consumed when sending a message

**Location**: `app/Services/MessageService.php::checkTippingRequirement()`

**Logic**:
```php
// Creator's settings
$requireTipForMessages = $receiver->getSetting('messaging', 'require_tip_for_messages', false);
$acceptMessagesFromFollowed = $receiver->getSetting('messaging', 'accept_messages_from_followed', true);

// Check mutual follow
$mutualFollow = $senderFollowsReceiver && $receiverFollowsSender;

// If requires tip and no mutual follow, tip is required
if ($requireTipForMessages && !$mutualFollow) {
    // Check for unconsumed tip
    $unconsumedTipExists = Tip::where('sender_id', $sender->id)
        ->where('recipient_id', $receiver->id)
        ->where('tippable_type', 'message')
        ->whereNull('tippable_id')
        ->exists();
    
    if (!$unconsumedTipExists) {
        throw new Exception('Tip required');
    }
}
```

---

## 6. PAYMENT & UNLOCKING

### 6.1 Purchase Flow

**When user clicks "Unlock" with price**:

```php
// Step 1: Process payment
$paymentResult = $paymentService->processPayment(
    $user,
    $amount,
    Transaction::ONE_TIME_PURCHASE,
    null,
    $creatorId,
    ['context' => 'purchase']
);

// Step 2: Create purchase record
Purchase::create([
    'user_id' => $user->id,
    'purchasable_id' => $post->id,
    'purchasable_type' => Post::class,
    'price' => $amount,
    'transaction_id' => $transaction->id
]);

// Step 3: Future permission checks will find this purchase
```

### 6.2 Permission Check After Purchase

**When loading post again**:
```php
// checkSinglePermission() finds the purchase
case 'add_price':
    $hasPaid = Purchase::where('user_id', $user->id)
        ->where('purchasable_id', $contentId)
        ->where('purchasable_type', $contentType)
        ->exists();
    
    return $hasPaid; // true → user has permission
```

---

## 7. PROBLEMS & ISSUES

### 7.1 Confusing Permission Logic

**Problem**: Complex nested logic that's hard to understand

**Current**:
```php
foreach ($content->permissionSets as $permissionSet) {
    $allPermissionsMet = true;
    foreach ($permissionSet->permissions as $permission) {
        if (!$this->checkSinglePermission($permission, $user, $ownerId)) {
            $allPermissionsMet = false;
            break;
        }
    }
    if ($allPermissionsMet) {
        return true; // User meets this set
    }
}
return false; // User didn't meet any set
```

**Issue**: 
- OR logic between sets hidden by nested loops
- AND logic within sets requires careful reading
- Not immediately clear how permissions combine

**Recommendation**: Use more explicit logic
```php
// Check if user meets ANY permission set
$meetsAnySet = $content->permissionSets->contains(function ($permissionSet) use ($user, $ownerId) {
    // Check if user meets ALL permissions in this set
    return $permissionSet->permissions->every(function ($permission) use ($user, $ownerId) {
        return $this->checkSinglePermission($permission, $user, $ownerId);
    });
});

return $meetsAnySet;
```

### 7.2 Multiple Database Queries in Loop

**Problem**: Permission checking runs N+1 queries

**Current Flow**:
```php
// For each post
foreach ($posts as $post) {
    // Check permission (runs queries inside)
    $post->user_has_permission = $this->permissionService->checkPermission($post, $user);
}
```

**Each checkPerrmission() runs**:
1. Check subscriptions
2. Check purchases  
3. Check follow relationship
4. Check time limits

**For 20 posts** = 80+ queries!

**Solution**: Batch load all checks
```php
// Batch load all data first
$subscriptions = Subscription::where('subscriber_id', $user->id)
    ->whereIn('creator_id', $creatorIds)
    ->get()
    ->keyBy('creator_id');

$purchases = Purchase::where('user_id', $user->id)
    ->whereIn('purchasable_id', $postIds)
    ->get()
    ->groupBy('purchasable_id');

// Then check using loaded data
foreach ($posts as $post) {
    $hasPermission = $this->checkWithLoadedData($post, $user, $subscriptions, $purchases);
}
```

### 7.3 Inefficient Preview Loading

**Problem**: Loading all previews even when user has permission

**Current**:
```php
$post->load('media.previews'); // Loads ALL previews
```

**Issue**: Unnecessary data if user already purchased

**Solution**: Conditional loading
```php
$post->load(['media' => function ($query) use ($user) {
    if ($this->hasPermission($post, $user)) {
        // Don't load previews if user has permission
        $query->without('previews');
    } else {
        // Load previews for locked content
        $query->with('previews');
    }
}]);
```

### 7.4 Location Filtering Complexity

**Problem**: Complex nested location filtering logic

**Current**: Lines 82-128 in FeedService.php

**Issue**: 
- Difficult to maintain
- Slow queries
- Hard to debug

**Solution**: Simplify to single location check
```php
protected function getBlockedCreatorIds(User $user, $creatorIds): Collection
{
    if (!$user->country_code) {
        return collect();
    }
    
    return User::whereIn('id', $creatorIds)
        ->whereHas('blockedLocations', function ($query) use ($user) {
            $query->where('country_code', $user->country_code);
        })
        ->pluck('id');
}
```

### 7.5 Confusing Purchase vs Transaction

**Problem**: Two tables for the same concept

**Tables**:
- `transactions` - Payment records
- `purchases` - Content purchases

**Issue**: Redundant data, confusing logic

**Current**: Check both tables
```php
// Check Purchase table
$hasPaid = Purchase::where(...)->exists();

// Also check Transaction table
if (!$hasPaid) {
    $transaction = Transaction::where(...)->first();
    if ($transaction) {
        Purchase::create([...]); // Create purchase from transaction
    }
}
```

**Recommendation**: Use single source of truth
- Always create Purchase when Transaction is approved
- Only check Purchase table for permission
- Transaction table is for payment tracking only

---

## 8. RECOMMENDATIONS FOR IMPROVEMENT

### 8.1 Permission System

#### A. Clarify Permission Logic
```php
public function checkPermission($content, User $user): bool
{
    // Owner always has access
    if ($this->isOwner($content, $user)) {
        return true;
    }
    
    // No media = no restrictions
    if ($content->media->isEmpty()) {
        return true;
    }
    
    // No permissions = public
    if ($content->permissionSets->isEmpty()) {
        return true;
    }
    
    // Check: User meets ANY permission set
    return $content->permissionSets->contains(function ($set) use ($user, $content) {
        // Where ALL permissions in set are met
        return $set->permissions->every(function ($permission) use ($user, $content) {
            return $this->checkSinglePermission($permission, $user, $content);
        });
    });
}
```

#### B. Cache Permission Checks
```php
$cacheKey = "permission:{$content->id}:{$user->id}";
return Cache::remember($cacheKey, 300, function () use ($content, $user) {
    return $this->checkPermission($content, $user);
});
```

#### C. Batch Permission Checks
```php
public function checkPermissionsBatch(Collection $posts, User $user): Collection
{
    // Load all data once
    $data = $this->loadUserDataForPosts($posts, $user);
    
    // Check all posts
    return $posts->map(function ($post) use ($user, $data) {
        $post->user_has_permission = $this->checkWithLoadedData($post, $user, $data);
        return $post;
    });
}
```

### 8.2 Content Loading

#### A. Optimize Queries
```php
// Use select() to load only needed fields
$posts = Post::select('id', 'content', 'user_id', 'created_at')
    ->with([
        'user:id,name,username,avatar',
        'media:id,mediable_id,type,url',
        'media.previews:id,media_id,url'
    ])
    ->get();
```

#### B. Conditional Relationships
```php
$posts->load([
    'media' => function ($query) use ($user) {
        if ($hasPermission) {
            $query->without('previews');
        }
    }
]);
```

#### C. Pagination
```php
// Use cursor pagination for better performance
$posts = Post::cursorPaginate(15);
```

### 8.3 Performance Improvements

#### A. Database Indexes
```php
// Add indexes for common queries
Schema::table('subscriptions', function (Blueprint $table) {
    $table->index(['subscriber_id', 'creator_id', 'status']);
    $table->index(['subscriber_id', 'end_date']);
});

Schema::table('purchases', function (Blueprint $table) {
    $table->index(['user_id', 'purchasable_id', 'purchasable_type']);
});
```

#### B. Query Optimization
```php
// Instead of N+1
foreach ($posts as $post) {
    $post->subscribers = $post->user->subscribers;
}

// Use eager loading
$posts->load('user.subscribers');
```

#### C. Redis Caching
```php
// Cache expensive operations
Cache::remember("user_subscriptions:{$user->id}", 300, function () use ($user) {
    return $user->subscriptions()->pluck('creator_id');
});
```

---

## 9. SUMMARY

### How It Works

1. **Creation**: User uploads media, sets permissions, system stores media + previews
2. **Loading**: System loads posts, checks permissions per post, filters by location
3. **Display**: Frontend shows previews if locked, full media if unlocked
4. **Unlocking**: User pays, system creates Purchase record, permissions update
5. **Tipping**: Separate from unlocking, used for messaging requirements

### Key Insights

✅ **Strengths**:
- Flexible permission system
- Supports multiple monetization strategies
- Media preview functionality
- Location-based filtering

❌ **Weaknesses**:
- Complex permission logic
- N+1 query problems
- Inefficient batch operations
- Confusing purchase/transaction relationship
- Overly complex location filtering

### Critical Improvements Needed

1. **Simplify permission logic** - Make OR/AND logic explicit
2. **Batch permission checks** - Load all data once, check in memory
3. **Optimize queries** - Add indexes, use eager loading
4. **Cache aggressively** - Cache permission checks, user data
5. **Consolidate tables** - Clarify purchase vs transaction
6. **Simplify location filtering** - Reduce complexity

---

*Last Updated: January 2025*

