<?php

namespace Ihasan\Bkash\Events;

use Ihasan\Bkash\Models\BkashPayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessful
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public BkashPayment $payment, public array $paymentData)
    {
    }
}
