# Laravel bKash
A Laravel package for integrating bKash Tokenized Payment Gateway.

## Installation

You can install the package via composer:

```bash
composer require theihasan/laravel-bkash
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-bkash-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-bkash-config"
```

This is the contents of the published config file:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | bKash Credentials
    |--------------------------------------------------------------------------
    |
    | Here you may configure your bKash credentials for the Tokenized Payment system.
    |
    */
    'sandbox' => env('BKASH_SANDBOX', true),
    
    'credentials' => [
        'app_key' => env('BKASH_APP_KEY', ''),
        'app_secret' => env('BKASH_APP_SECRET', ''),
        'username' => env('BKASH_USERNAME', ''),
        'password' => env('BKASH_PASSWORD', ''),
    ],
    
    'sandbox_base_url' => env('SANDBOX_BASE_URL', 'https://tokenized.sandbox.bka.sh'),
    'live_base_url' => env('LIVE_BASE_URL', 'https://tokenized.pay.bka.sh'),
    
    'version' => 'v1.2.0-beta',
    
    'cache' => [
        'token_lifetime' => 3600, // 1 hour in seconds
    ],
    
    'default_currency' => 'BDT',
    'default_intent' => 'sale',
];
```

## Configuration

Add the following environment variables to your `.env` file:

```
BKASH_SANDBOX=true
BKASH_APP_KEY=your-app-key
BKASH_APP_SECRET=your-app-secret
BKASH_USERNAME=your-username
BKASH_PASSWORD=your-password
SANDBOX_BASE_URL=https://tokenized.sandbox.bka.sh
LIVE_BASE_URL=https://tokenized.pay.bka.sh
```

## Usage

### Initiating a Payment

```php
use Ihasan\Bkash\Facades\Bkash;

// Create a payment
$paymentData = [
    'amount' => '100',
    'payer_reference' => 'customer123', // optional
    'callback_url' => route('bkash.callback'),
    'merchant_invoice_number' => 'INV-123456',
];

try {
    $response = Bkash::createPayment($paymentData);
    
    // Redirect user to bKash payment page
    return redirect()->away($response['bkashURL']);
} catch (\Exception $e) {
    // Handle exception
    return back()->with('error', $e->getMessage());
}
```

### Handling Callback

In your callback route:

```php
use Ihasan\Bkash\Facades\Bkash;

public function handleCallback(Request $request)
{
    $paymentId = $request->input('paymentID');
    $status = $request->input('status');

    if ($status === 'success') {
        try {
            $response = Bkash::executePayment($paymentId);
            
            // Payment successful, do something with $response
            return redirect()->route('payment.success', [
                'transaction_id' => $response['trxID'],
            ]);
        } catch (\Exception $e) {
            // Handle exception
            return redirect()->route('payment.failed')->with('error', $e->getMessage());
        }
    } else {
        // Payment failed or cancelled
        return redirect()->route('payment.failed')->with('error', 'Payment was not successful');
    }
}
```

### Getting a Token Manually

```php
use Ihasan\Bkash\Facades\Bkash;

$token = Bkash::getToken();
```

### Refreshing a Token

```php
use Ihasan\Bkash\Facades\Bkash;

$token = Bkash::refreshToken();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Md Abul Hassan](https://github.com/theihasan)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
