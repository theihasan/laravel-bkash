<?php

namespace Ihasan\Bkash\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ihasan\Bkash\Bkash
 *
 * @method static string getToken()
 * @method static string refreshToken()
 * @method static array createPayment(array $data)
 * @method static array executePayment(string $paymentId)
 */
class Bkash extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Ihasan\Bkash\Bkash::class;
    }
}
