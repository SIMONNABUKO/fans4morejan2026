<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewMessage;
use App\Mail\SubscriptionPurchase;
use App\Mail\SubscriptionRenew;
use App\Mail\SubscriptionGiftLinkPurchase;
use App\Mail\MediaPurchase;
use App\Mail\MediaBundlePurchase;
use App\Mail\MediaLike;
use App\Mail\PostReply;
use App\Mail\PostLike;
use App\Mail\TipReceived;
use App\Mail\NewFollower;
use App\Mail\StreamLive;
use App\Mail\MediaLikeNotification;
use App\Mail\PostLikeNotification;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Log;
use App\Mail\EmailVerificationCodeMail;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;

class EmailService
{
    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        
        // Register event listeners for email events
        Event::listen(MessageSending::class, function ($event) {
            Log::info('Attempting to send email', [
                'to' => $event->message->getTo(),
                'subject' => $event->message->getSubject(),
                'smtp_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                    'username' => config('mail.mailers.smtp.username'),
                    'stream_options' => [
                        'ssl' => [
                            'verify_peer' => config('mail.mailers.smtp.stream.ssl.verify_peer'),
                            'verify_peer_name' => config('mail.mailers.smtp.stream.ssl.verify_peer_name'),
                            'allow_self_signed' => config('mail.mailers.smtp.stream.ssl.allow_self_signed'),
                            'verify_depth' => config('mail.mailers.smtp.stream.ssl.verify_depth'),
                            'cafile' => config('mail.mailers.smtp.stream.ssl.cafile'),
                            'capath' => config('mail.mailers.smtp.stream.ssl.capath'),
                            'peer_name' => config('mail.mailers.smtp.stream.ssl.peer_name'),
                            'crypto_method' => config('mail.mailers.smtp.stream.ssl.crypto_method'),
                        ]
                    ]
                ]
            ]);

            // Log OpenSSL information
            Log::info('OpenSSL Configuration', [
                'version' => OPENSSL_VERSION_TEXT,
                'default_cert_file' => openssl_get_cert_locations()['default_cert_file'] ?? null,
                'default_cert_file_env' => openssl_get_cert_locations()['default_cert_file_env'] ?? null,
                'default_cert_dir' => openssl_get_cert_locations()['default_cert_dir'] ?? null,
                'default_cert_dir_env' => openssl_get_cert_locations()['default_cert_dir_env'] ?? null,
                'ini_cafile' => ini_get('openssl.cafile'),
                'ini_capath' => ini_get('openssl.capath'),
            ]);
        });

        Event::listen(MessageSent::class, function ($event) {
            Log::info('Email sent successfully', [
                'to' => $event->message->getTo(),
                'subject' => $event->message->getSubject()
            ]);
        });
    }

    public function sendNewMessageNotification(User $user, $messageData)
    {
        try {
            Log::info('Preparing to send new message notification', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            if ($this->shouldSendNotification($user, 'new_messages')) {
                Mail::to($user)->send(new NewMessage($messageData));
            } else {
                Log::info('New message notification skipped due to user settings', [
                    'user_id' => $user->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send new message notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    // public function sendSubscriptionPurchaseNotification(User $user, $subscriptionData)
    // {
    //     if ($this->shouldSendNotification($user, 'subscription_purchases')) {
    //         Mail::to($user)->send(new SubscriptionPurchase($subscriptionData));
    //     }
    // }

    // public function sendSubscriptionRenewNotification(User $user, $subscriptionData)
    // {
    //     if ($this->shouldSendNotification($user, 'subscription_renews')) {
    //         Mail::to($user)->send(new SubscriptionRenew($subscriptionData));
    //     }
    // }

    // public function sendSubscriptionGiftLinkPurchaseNotification(User $user, $giftLinkData)
    // {
    //     if ($this->shouldSendNotification($user, 'subscription_gift_link_purchases')) {
    //         Mail::to($user)->send(new SubscriptionGiftLinkPurchase($giftLinkData));
    //     }
    // }

    // public function sendMediaPurchaseNotification(User $user, $mediaPurchaseData)
    // {
    //     if ($this->shouldSendNotification($user, 'media_purchases')) {
    //         Mail::to($user)->send(new MediaPurchase($mediaPurchaseData));
    //     }
    // }

    // public function sendMediaBundlePurchaseNotification(User $user, $bundlePurchaseData)
    // {
    //     if ($this->shouldSendNotification($user, 'media_bundle_purchases')) {
    //         Mail::to($user)->send(new MediaBundlePurchase($bundlePurchaseData));
    //     }
    // }

    public function sendMediaLikeNotification(User $user, $likeData)
    {
        if ($this->shouldSendNotification($user, 'media_likes')) {
            Mail::to($user)->send(new MediaLikeNotification($likeData));
        }
    }

    // public function sendPostReplyNotification(User $user, $replyData)
    // {
    //     if ($this->shouldSendNotification($user, 'post_replies')) {
    //         Mail::to($user)->send(new PostReply($replyData));
    //     }
    // }

    public function sendPostLikeNotification(User $user, $likeData)
    {
        if ($this->shouldSendNotification($user, 'post_likes')) {
            Mail::to($user)->send(new PostLikeNotification($likeData));
        }
    }

    // public function sendTipReceivedNotification(User $user, $tipData)
    // {
    //     if ($this->shouldSendNotification($user, 'tips_received')) {
    //         Mail::to($user)->send(new TipReceived($tipData));
    //     }
    // }

    public function sendNewFollowerNotification(User $user, $followerData)
    {
        Log::info('Mail config for new follower notification', [
            'driver' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ]);
        Log::info('Attempting to send new follower notification', ['user_id' => $user->id, 'follower_id' => $followerData['follower_id']]);
    
        if ($this->shouldSendNotification($user, 'new_followers')) {
            Log::info('Sending new follower notification email', ['user_id' => $user->id, 'follower_id' => $followerData['follower_id']]);
            Mail::to($user)->send(new NewFollower($followerData));
        } else {
            Log::info('New follower notification email not sent due to user settings', ['user_id' => $user->id]);
        }
    }

    // public function sendStreamLiveNotification(User $user, $streamData)
    // {
    //     if ($this->shouldSendNotification($user, 'stream_live')) {
    //         Mail::to($user)->send(new StreamLive($streamData));
    //     }
    // }

    public function sendEmailVerificationCode(User $user, $code)
    {
        try {
            Log::info('Preparing to send email verification code', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            $user->notify(new \App\Notifications\EmailVerificationCodeNotification($code));

            Log::info('Email verification code notification queued', [
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email verification code', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function sendPasswordResetEmail(User $user, string $resetUrl): void
    {
        try {
            Log::info('Preparing to send password reset email', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            Mail::to($user)->send(new PasswordResetMail($user, $resetUrl));
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function shouldSendNotification(User $user, string $notificationType): bool
    {
        $should_send = $this->settingsService->getUserSetting($user, 'emailNotifications', $notificationType, true);
        Log::info('Checking if notification should be sent', [
            'user_id' => $user->id,
            'notification_type' => $notificationType,
            'should_send' => $should_send
        ]);
        return $should_send;
    }
}
