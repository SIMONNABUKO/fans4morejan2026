# Application Modules Documentation

This document provides a comprehensive overview of the modules in the Fans-API application, detailing their purpose, key components, and relationships.

## Overview

The application follows a standard Laravel architecture:
-   **Controllers**: Handle HTTP requests and responses (`app/Http/Controllers`).
-   **Services**: Contain the core business logic (`app/Services`).
-   **Models**: Represent database entities (`app/Models`).
-   **Routes**: Define the API endpoints (`routes/api.php`).

## Module Breakdown

### 1. Authentication & User Management
**Purpose**: Manages user registration, authentication, profile management, and settings.

*   **Key Components**:
    *   **Controllers**: `Auth\LoginController`, `Auth\UserRegistrationController`, `UserController`, `UserSettingController`, `UserLocationController`.
    *   **Services**: `LoginService`, `UserRegistrationService`, `UserLocationService`, `SettingsService`.
    *   **Models**: `User`, `UserSetting`, `UserPermission`.
*   **Functionality**:
    *   User signup and login (Sanctum).
    *   Profile updates (display name, email, password).
    *   User settings management (privacy, notifications).
    *   Location tracking and updates.

### 2. Content Management (Feed & Posts)
**Purpose**: Handles the creation, retrieval, and interaction with user-generated content.

*   **Key Components**:
    *   **Controllers**: `PostController`, `FeedController`, `MediaController`, `MediaAlbumController`, `PostTagController`, `LikeController`, `CommentController`.
    *   **Services**: `PostService`, `FeedService`, `MediaService`, `LikeService`, `CommentService`, `PostTagService`.
    *   **Models**: `Post`, `Media`, `MediaAlbum`, `Comment`, `Like`, `PostTag`.
*   **Functionality**:
    *   Creating and editing posts with media.
    *   Feed generation (chronological, personalized).
    *   Media upload and management (albums).
    *   Interactions: Liking and commenting on posts.
    *   Tagging users in posts.

### 3. Messaging System
**Purpose**: Facilitates direct communication between users, including mass messaging and automation.

*   **Key Components**:
    *   **Controllers**: `MessageController`, `MessageDraftController`, `MessageTemplateController`, `ScheduledMessageController`, `MassMessageController`, `AutomatedMessageController`, `DMPermissionController`.
    *   **Services**: `MessageService`, `AutomatedMessageService`.
    *   **Models**: `Message`, `MessageDraft`, `MessageTemplate`, `ScheduledMessage`, `MassMessage`, `AutomatedMessage`, `DMPermission`.
*   **Functionality**:
    *   1-on-1 direct messaging.
    *   Mass messaging campaigns.
    *   Scheduling messages for future delivery.
    *   Message templates and drafts.
    *   Automated welcome messages.
    *   DM permission settings (price to message).

### 4. Subscriptions & Tiers
**Purpose**: Manages creator subscription plans and user subscriptions.

*   **Key Components**:
    *   **Controllers**: `SubscriptionController`, `TierController`, `SubscriptionDiscountController`.
    *   **Services**: `TierService`.
    *   **Models**: `Subscription`, `Tier`, `SubscriptionDiscount`.
*   **Functionality**:
    *   Creators define subscription tiers (benefits, pricing).
    *   Users subscribe to tiers.
    *   Subscription renewal and cancellation.
    *   Discount management for tiers.

### 5. Financials (Wallet, Transactions, Payouts)
**Purpose**: Handles all monetary transactions, wallet management, and creator payouts.

*   **Key Components**:
    *   **Controllers**: `WalletController`, `WalletHistoryController`, `TransactionController`, `PayoutMethodController`, `PayoutRequestController`, `EarningsController`, `TipController`.
    *   **Services**: `WalletService`, `TransactionService`, `PaymentService`, `TipService`.
    *   **Models**: `Wallet`, `WalletHistory`, `Transaction`, `Payout`, `PayoutMethod`, `PayoutRequest`, `Tip`, `Purchase`.
*   **Functionality**:
    *   Wallet balance management (add/subtract funds).
    *   Processing transactions (tips, purchases, subscriptions).
    *   Tracking earnings and statistics.
    *   Payout method configuration and withdrawal requests.

### 6. Social Interactions
**Purpose**: Manages social connections and user organization.

*   **Key Components**:
    *   **Controllers**: `FollowController`, `BookmarkController`, `ListController`, `ReportController`.
    *   **Services**: `FollowService`, `BookmarkService`, `ListService`, `ReportService`.
    *   **Models**: `Bookmark`, `Lists`, `Report`.
*   **Functionality**:
    *   Following/Unfollowing users.
    *   Bookmarking posts and media.
    *   Creating user lists (e.g., "Favorites").
    *   Reporting content or users.

### 7. Referrals & Tracking
**Purpose**: Manages the referral system and tracks link performance.

*   **Key Components**:
    *   **Controllers**: `ReferralController`, `TrackingLinkController`.
    *   **Services**: `ReferralService`, `TrackingLinkService`.
    *   **Models**: `Referral`, `TrackingLink`, `TrackingLinkAction`.
*   **Functionality**:
    *   Generating referral links.
    *   Tracking clicks and conversions.
    *   Calculating referral earnings.

### 8. Notifications
**Purpose**: Alerts users about relevant activities.

*   **Key Components**:
    *   **Controllers**: `NotificationController`.
    *   **Services**: `NotificationService`.
    *   **Functionality**:
    *   Fetching notifications.
    *   Marking notifications as read.

### 9. System & Configuration
**Purpose**: Manages global system settings and legal documents.

*   **Key Components**:
    *   **Controllers**: `BlockedLocationController`, `TermsAndConditionController`, `PrivacyPolicyController`.
    *   **Services**: `BlockedLocationService`.
    *   **Models**: `BlockedLocation`, `TermsAndCondition`, `PrivacyPolicy`.
*   **Functionality**:
    *   Geo-blocking specific locations.
    *   Managing Terms of Service and Privacy Policy content.

### 10. Admin Panel
**Purpose**: Administrative control over the platform.

*   **Key Components**:
    *   **Controllers**: `AdminAuthController`, `Admin\UserController`, `Admin\MediaController`.
    *   **Functionality**:
    *   Admin authentication.
    *   User management (ban, verify).
    *   Content moderation.

## Module Relationships

*   **User -> All Modules**: The `User` model is central and related to almost every other module (Posts, Messages, Wallet, Subscriptions, etc.).
*   **Content -> Financials**: Posts and Media can be monetized (pay-to-view), linking Content to Transactions.
*   **Subscriptions -> Financials**: Subscriptions generate recurring Transactions.
*   **Messaging -> Financials**: Messages can be locked (pay-to-unlock), linking Messaging to Transactions.
*   **Notifications -> All Modules**: Actions in other modules (e.g., new follower, new tip) trigger Notifications.

## Data Flow

1.  **Request**: Client sends HTTP request to a Route.
2.  **Controller**: Route dispatches to a Controller method.
3.  **Validation**: Controller validates input (often using FormRequests).
4.  **Service**: Controller calls a Service to perform business logic.
5.  **Model**: Service interacts with Models to query/persist data.
6.  **Response**: Controller returns a JSON response (often using Resources).
