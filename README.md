# Laravel bKash
A Laravel package for integrating bKash Tokenized Payment Gateway.

## Installation

You can install the package via composer:

```bash
composer require theihasan/laravel-bkash
```
Run this command for quick setup and test bkash api connection
```php
php artisan bkash:setup --test
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

#### **Complete Payment Flow Example**

```php
<?php

namespace App\Http\Controllers;

use Ihasan\Bkash\Facades\Bkash;
use Illuminate\Http\Request;

class BkashPaymentController extends Controller
{
    /**
     * Initiate a payment
     */
    public function initiatePayment(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'invoice_number' => 'required|string',
            ]);

            // Prepare payment data
            $paymentData = [
                'amount' => $request->amount,
                'merchant_invoice_number' => $request->invoice_number,
                'callback_url' => route('bkash.callback'),
                'payer_reference' => auth()->id(), // Optional
            ];

            // Create the payment
            $response = Bkash::createPayment($paymentData);
            
            // Redirect user to bKash payment page
            return redirect()->away($response['bkashURL']);
        } catch (\Ihasan\Bkash\Exceptions\PaymentCreateException $e) {
            // Handle payment creation failure
            return back()->with('error', 'Payment creation failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Handle other exceptions
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Handle the callback from bKash
     */
    public function handleCallback(Request $request)
    {
        $paymentId = $request->input('paymentID');
        $status = $request->input('status');

        if ($status === 'success') {
            try {
                // Execute the payment
                $response = Bkash::executePayment($paymentId);
                
                // Update your order status
                // For example:
                // $order = Order::where('invoice_number', $response['merchantInvoiceNumber'])->first();
                // $order->update(['status' => 'paid', 'transaction_id' => $response['trxID']]);
                
                return redirect()->route('payment.success', [
                    'transaction_id' => $response['trxID'],
                ]);
            } catch (\Ihasan\Bkash\Exceptions\PaymentExecuteException $e) {
                // Handle payment execution failure
                return redirect()->route('payment.failed')->with('error', 'Payment execution failed: ' . $e->getMessage());
            }
        } else {
            // Payment failed or cancelled by user
            return redirect()->route('payment.failed')->with('error', 'Payment was not successful');
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus(Request $request)
    {
        try {
            $paymentId = $request->input('payment_id');
            $response = Bkash::queryPayment($paymentId);
            
            return response()->json([
                'success' => true,
                'status' => $response['transactionStatus'],
                'data' => $response
            ]);
        } catch (\Ihasan\Bk
        } catch (\Ihasan\Bkash\Exceptions\PaymentQueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a refund
     */
    public function refundPayment(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'payment_id' => 'required|string',
                'trx_id' => 'required|string',
                'amount' => 'required|numeric|min:1',
                'reason' => 'nullable|string',
            ]);

            // Prepare refund data
            $refundData = [
                'payment_id' => $request->payment_id,
                'trx_id' => $request->trx_id,
                'amount' => $request->amount,
                'reason' => $request->reason ?? 'Customer requested refund',
            ];

            // Process the refund
            $response = Bkash::refundPayment($refundData);
            
            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => $response
            ]);
        } catch (\Ihasan\Bkash\Exceptions\RefundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refund failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show payment success page
     */
    public function showSuccessPage(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        
        // You can fetch payment details from your database
        // $payment = BkashPayment::where('trx_id', $transactionId)->first();
        
        return view('payments.success', [
            'transaction_id' => $transactionId
        ]);
    }

    /**
     * Show payment failure page
     */
    public function showFailurePage(Request $request)
    {
        $error = $request->session()->get('error');
        
        return view('payments.failed', [
            'error' => $error
        ]);
    }
}

```
#### **Routes Example**
```php
use App\Http\Controllers\BkashPaymentController;

Route::post('/bkash/payment', [BkashPaymentController::class, 'initiatePayment'])->name('bkash.payment');
Route::get('/bkash/callback', [BkashPaymentController::class, 'handleCallback'])->name('bkash.callback');
Route::get('/bkash/status', [BkashPaymentController::class, 'checkStatus'])->name('bkash.status');
Route::post('/bkash/refund', [BkashPaymentController::class, 'refundPayment'])->name('bkash.refund');
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
