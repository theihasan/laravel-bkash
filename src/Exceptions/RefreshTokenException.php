<?php

namespace Ihasan\Bkash\Exceptions;

class RefreshTokenException extends \Exception
{
    public function __construct($message = 'Refresh Token Failed', $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
