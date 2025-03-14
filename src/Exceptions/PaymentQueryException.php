<?php

namespace Ihasan\Bkash\Exceptions;

class PaymentQueryException extends \Exception
{
    public function __construct($message = 'Payment Query Failed', $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
