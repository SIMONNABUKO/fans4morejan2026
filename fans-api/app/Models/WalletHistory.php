<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'amount',
        'balance_type',
        'transaction_type',
        'payment_type',
        'description',
        'status',
        'reference_id',
        'transactionable_type',
        'transactionable_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Transaction type constants
    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';
    const TYPE_TRANSFER = 'transfer';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    // Balance type constants
    const BALANCE_TOTAL = 'total';
    const BALANCE_PENDING = 'pending';
    const BALANCE_AVAILABLE = 'available';



    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }



    public function isCredit()
    {
        return $this->transaction_type === self::TYPE_CREDIT;
    }

    public function isDebit()
    {
        return $this->transaction_type === self::TYPE_DEBIT;
    }

    public function isTransfer()
    {
        return $this->transaction_type === self::TYPE_TRANSFER;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getFormattedAmount()
    {
        $prefix = $this->isCredit() ? '+' : ($this->isDebit() ? '-' : '');
        return $prefix . '$' . number_format($this->amount, 2);
    }

    /**
     * Get the transaction type for display
     * Uses the related transaction's type if available, otherwise uses the wallet history's transaction_type
     */
    public function getDisplayTransactionType()
    {
        if ($this->transaction && $this->transaction->type) {
            return $this->transaction->type;
        }

        return $this->transaction_type;
    }

    /**
     * Get the user that owns the wallet history.
     */
    public function user(): BelongsTo
    {

        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent transactionable model (Transaction, PayoutRequest, etc).
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }
}
