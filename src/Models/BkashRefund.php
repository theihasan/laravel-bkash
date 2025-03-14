<?php

namespace Ihasan\Bkash\Models;

use Illuminate\Database\Eloquent\Model;

class BkashRefund extends Model
{
    protected $fillable = [
        'payment_id',
        'original_trx_id',
        'refund_trx_id',
        'amount',
        'currency',
        'transaction_status',
        'completed_time',
        'reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'completed_time' => 'datetime',
    ];

    /**
     * Get the payment that was refunded.
     */
    public function payment()
    {
        return $this->belongsTo(BkashPayment::class, 'payment_id', 'payment_id');
    }
}
