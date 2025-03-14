<?php

namespace Ihasan\Bkash\Exceptions;

class RefundException extends \Exception
{
    public function __construct($message = 'Refund Failed', $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
