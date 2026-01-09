<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

class TipService
{
    protected $statsService;

    public function __construct(StatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    public function sendTip(User $sender, Model $tippable, float $amount, ?int $trackingLinkId = null)
    {
        return DB::transaction(function () use ($sender, $tippable, $amount, $trackingLinkId) {
            $tip = Tip::create([
                'sender_id' => $sender->id,
                'recipient_id' => $tippable->user_id,
                'tippable_id' => $tippable->id,
                'tippable_type' => get_class($tippable),
                'amount' => $amount,
            ]);

            $this->statsService->addTip($tippable, $amount);

            // Process the payment with tracking link if available
            $paymentService = app(PaymentService::class);
            $additionalData = [
                'payment_method' => 'wallet', // Default to wallet for tips
                'context' => 'tip'
            ];
            
            if ($trackingLinkId) {
                $additionalData['tracking_link_id'] = $trackingLinkId;
            }

            $paymentResult = $paymentService->processPayment(
                $sender,
                $amount,
                Transaction::TIP,
                null,
                $tippable->user_id,
                $additionalData,
                Tip::class,
                $tip->id
            );

            return $tip;
        });
    }

    public function getTipsForUser(User $user)
    {
        return Tip::where('recipient_id', $user->id)->get();
    }

    public function getTipsForTippable(Model $tippable)
    {
        return Tip::where('tippable_id', $tippable->id)
            ->where('tippable_type', get_class($tippable))
            ->get();
    }
}

