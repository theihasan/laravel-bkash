# Laravel bKash
A Laravel package for integrating bKash Tokenized Payment Gateway.

## Installation

You can install the package via composer:

```bash
composer require theihasan/laravel-bkash
```
Run this command for quick setup and test bkash api connection
```php
php artisan bkash:setup --test --publish-views --publish-controllers
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

### Initiating and Handling callback of a Payment

```php
use Ihasan\Bkash\Facades\Bkash;

// Create a payment
$paymentData = [
    'amount' => '100',
    'payer_reference' => 'customer123', // optional
    'callback_url' => route('bkash.callback'), // Don't change this. Otherwise automatic payment execution won't work. You have handle callback manually 
    'merchant_invoice_number' => 'INV-123456',
];

try {
    $response = Bkash::createPayment($paymentData);
    
    // Redirect user to bKash payment page
    return redirect()->away($response);
} catch (\Exception $e) {
    // Handle exception
    return back()->with('error', $e->getMessage());
}
```

### Handling Callback

If you want to handle callback in your own way you may follow these steps

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

#### **Error Handling**

The package provides specific exception classes for different types of errors:
1. **TokenGenerationException:** Thrown when the package fails to generate a token

```php
try {
    $token = Bkash::getToken();
} catch (\Ihasan\Bkash\Exceptions\TokenGenerationException $e) {
    // Handle token generation failure
    // Common causes: Invalid credentials, API connectivity issues
}
```
2. **RefreshTokenException:** Thrown when the package fails to refresh a token
```php
try {
    $token = Bkash::refreshToken();
} catch (\Ihasan\Bkash\Exceptions\RefreshTokenException $e) {
    // Handle token refresh failure
    // Common causes: Expired refresh token, API connectivity issues
}
```
3. **PaymentCreationException:** Thrown when the package fails to create a payment
```php
try {
    $response = Bkash::createPayment($paymentData);
} catch (\Ihasan\Bkash\Exceptions\PaymentCreateException $e) {
    // Handle payment creation failure
    // Common causes: Invalid amount, invalid callback URL, API connectivity issues
}
```
4. **PaymentExecutionException:** Thrown when the package fails to execute a payment
```php
try {
    $response = Bkash::executePayment($paymentId);
} catch (\Ihasan\Bkash\Exceptions\PaymentExecuteException $e) {
    // Handle payment execution failure
    // Common causes: Payment already executed, payment cancelled, insufficient balance
}
```
5. **PaymentQueryException:** Thrown when the package fails to query a payment
```php
try {
    $response = Bkash::queryPayment($paymentId);
} catch (\Ihasan\Bkash\Exceptions\PaymentQueryException $e) {
    // Handle payment query failure
    // Common causes: Invalid payment ID, API connectivity issues
}
```
6. **RefundException:** Thrown when the package fails to refund a payment
```php
try {
    $response = Bkash::refundPayment($refundData);
} catch (\Ihasan\Bkash\Exceptions\RefundException $e) {
    // Handle refund failure
    // Common causes: Payment not found, already refunded, refund amount exceeds payment amount
}
```

## Simplified Payment Flow

This package includes built-in routes and controllers to handle the bKash payment flow with minimal code.

### Using the Built-in Payment Flow

The package provides these routes out of the box:

- `GET /bkash/callback` - Handle bKash callback
- `GET /bkash/success` - Display success page
- `GET /bkash/failed` - Display failed page


#### Customizing Redirect URLs

If you want to use your own success and failure pages, you can configure them in the `bkash.php` config file:

```php
'redirect_urls' => [
    'success' => '/payment/success',
    'failed' => '/payment/failed',
],
```

#### Disabling Built-in Routes

If you prefer to implement your own routes and controllers, you can disable the built-in routes:

```php
'routes' => [
    'enabled' => false,
],
```

### Manual Implementation

You can still use the core methods directly if you need more control:

```php
use Ihasan\Bkash\Facades\Bkash;

// Create payment
$paymentData = [
    'amount' => '100',
    'payer_reference' => '01XXXXXXXXX',
    'callback_url' => route('your.callback.route'),
    'merchant_invoice_number' => 'INV-12345',
];
$response = Bkash::createPayment($paymentData);
return redirect()->away($response);

// Execute payment
$response = Bkash::executePayment($paymentId);

// Query payment
$response = Bkash::queryPayment($paymentId);

// Refund payment
$refundData = [
    'payment_id' => $paymentId,
    'trx_id' => $trxId,
    'amount' => '100',
    'reason' => 'Customer requested refund',
];
$response = Bkash::refundPayment($refundData);
```

## Customizing Views and Controllers

### Publishing Views

If you want to customize the payment success and failure pages, you can publish the views:

```bash
php artisan bkash:setup --publish-views
```

This will copy the view files to `resources/views/vendor/bkash/` where you can modify them according to your needs.

### Publishing Controllers

If you need more control over the payment flow, you can publish the controllers:

```bash
php artisan bkash:setup --publish-controllers
```

This will copy the controllers to `app/Http/Controllers/Vendor/Bkash/`. After publishing, you'll need to:

1. Update the namespace of the controllers to match your application's namespace structure


Alternatively, you can disable the built-in routes in the config file and define your own routes:

```php
// In config/bkash.php
'routes' => [
    'enabled' => false,
],
```

#### **Routes Example**
```php
use App\Http\Controllers\BkashPaymentController;

Route::get('/bkash/callback', [BkashPaymentController::class, 'handleCallback'])->name('bkash.callback');
Route::get('/payment/success', [BkashPaymentController::class, 'showSuccessPage'])->name('payment.success');
Route::get('/payment/failed', [BkashPaymentController::class, 'showFailurePage'])->name('payment.failed');
```

#### **What's Next**

I am working on the next version with additional features:

* Agreement-based recurring payments
* Webhook support

I welcome your feedback and contributions to make Laravel bKash even better!
## Testing

```bash
composer test
```

## Credits

- [Md Abul Hassan](https://github.com/theihasan)
- Special thanks to [Ahmed Shamim Hasan Shaon](https://github.com/me-shaon)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
