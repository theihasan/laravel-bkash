<?php

namespace Ihasan\Bkash\Exceptions;

class TokenGenerationException extends \Exception
{
    public function __construct($message = 'Token Generation Failed', $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
