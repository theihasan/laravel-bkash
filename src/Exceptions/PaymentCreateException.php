<?php

namespace Ihasan\Bkash\Exceptions;

class PaymentCreateException extends \Exception
{
    public function __construct($message = 'Payment Creation Failed', $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
