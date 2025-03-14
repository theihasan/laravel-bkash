<?php

namespace Ihasan\Bkash\Models;

use Illuminate\Database\Eloquent\Model;

class BkashPayment extends Model
{
    protected $fillable = [
        'payment_id',
        'trx_id',
        'agreement_id',
        'payer_reference',
        'customer_msisdn',
        'amount',
        'currency',
        'intent',
        'merchant_invoice_number',
        'transaction_status',
        'payment_create_time',
        'payment_execute_time',
        'agreement_execute_time',
        'agreement_status',
        'status_code',
        'status_message',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_create_time' => 'datetime',
        'payment_execute_time' => 'datetime',
        'agreement_execute_time' => 'datetime',
    ];

    /**
     * Get the refunds for the payment.
     */
    public function refunds()
    {
        return $this->hasMany(BkashRefund::class, 'payment_id', 'payment_id');
    }
}
