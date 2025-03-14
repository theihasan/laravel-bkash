# Laravel bKash
A Laravel package for integrating bKash Tokenized Payment Gateway.

## Installation

You can install the package via composer:

```bash
composer require theihasan/laravel-bkash
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="bkash-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="bkash-config"
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
### Querying Payment Status

You can check the status of a payment using the `queryPayment` method:

```php
use Ihasan\Bkash\Facades\Bkash;

try {
    $response = Bkash::queryPayment($paymentId);
    
    // Check payment status
    $status = $response['transactionStatus'];
    
    if ($status === 'Completed') {
        // Payment is completed
        return response()->json([
            'success' => true,
            'message' => 'Payment completed successfully',
            'data' => $response
        ]);
    } else {
        // Payment is not completed
        return response()->json([
            'success' => false,
            'message' => 'Payment is ' . $status,
            'data' => $response
        ]);
    }
} catch (\Exception $e) {
    // Handle exception
    return response()->json([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
}
```

### Refunding a Payment

You can refund a payment using the `refundPayment` method:

```php
use Ihasan\Bkash\Facades\Bkash;

try {
    $refundData = [
        'payment_id' => $paymentId,
        'trx_id' => $transactionId,
        'amount' => '50', // Amount to refund (can be partial)
        'reason' => 'Customer requested refund', // Optional
        'sku' => 'PROD-123' // Optional
    ];
    
    $response = Bkash::refundPayment($refundData);
    
    return response()->json([
        'success' => true,
        'message' => 'Refund processed successfully',
        'data' => $response
    ]);
} catch (\Exception $e) {
    // Handle exception
    return response()->json([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
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
## Available Methods

The package provides the following methods:

- `getToken()` - Generate a new authorization token
- `refreshToken()` - Refresh an existing token
- `createPayment(array $data)` - Create a new payment
- `executePayment(string $paymentId)` - Execute a payment after user authorization
- `queryPayment(string $paymentId)` - Check the status of a payment
- `refundPayment(array $data)` - Refund a completed payment (can be partial)

## Models

The package includes two models:

### BkashPayment

This model stores information about payments:

```php
use Ihasan\Bkash\Models\BkashPayment;

// Find a payment by ID
$payment = BkashPayment::where('payment_id', $paymentId)->first();

// Get all payments
$payments = BkashPayment::all();

// Get payments with a specific status
$completedPayments = BkashPayment::where('transaction_status', 'Completed')->get();
```

### BkashRefund

This model stores information about refunds:

```php
use Ihasan\Bkash\Models\BkashRefund;

// Find a refund by transaction ID
$refund = BkashRefund::where('refund_trx_id', $refundTrxId)->first();

// Get all refunds for a payment
$refunds = BkashRefund::where('payment_id', $paymentId)->get();
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
