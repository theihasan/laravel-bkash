<?php

namespace Ihasan\Bkash\Exceptions;

class PaymentExecuteException extends \Exception
{
    public function __construct($message = 'Payment Execution Failed', $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
