# Laravel bKash

A Laravel package for integrating the bKash Tokenized Payment Gateway into your Laravel application with ease.

---

## Table of Contents

1. [Overview](#overview)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Setup and Publishing Assets](#setup-and-publishing-assets)
6. [Usage](#usage)
    - [Creating a Payment](#creating-a-payment)
    - [Handling the Callback](#handling-the-callback)
    - [Querying Payment Status](#querying-payment-status)
    - [Refunding a Payment](#refunding-a-payment)
    - [Token Management](#token-management)
7. [Built-in Payment Flow](#built-in-payment-flow)
8. [Error Handling](#error-handling)
9. [Customizing Views and Controllers](#customizing-views-and-controllers)
10. [Test Credentials](#test-credentials)
11. [Testing](#testing)
12. [Contributing](#contributing)
13. [Credits and License](#credits-and-license)

---

## Overview

The **Laravel bKash** package provides an easy-to-use solution for integrating bKash’s tokenized payment gateway with your Laravel application. It simplifies payment processing including token generation, payment creation, execution, status queries, and refunds.

Key features include:

- Seamless installation via Composer.
- Quick configuration through published config files.
- Built-in controllers and routes for an out-of-the-box payment flow.
- Manual methods for full control over the payment process.
- Robust and detailed error handling using dedicated exception classes.

---

## Requirements

- **PHP:** Minimum version 8.0  
- **Laravel:** Version greater than 8.x  
- **cURL Extension:** Enabled

---

## Installation

Install the package via Composer:

```bash
composer require theihasan/laravel-bkash
```

After installation, run the setup command to test the bKash API connection and to publish views and controllers:

```bash
php artisan bkash:setup --test --publish-views --publish-controllers
```

Alternatively, you can publish individual assets:

**Migrations:**
  ```bash
  php artisan vendor:publish --tag="bkash-migrations"
  php artisan migrate
  ```
**Configuration:**
  ```bash
  php artisan vendor:publish --tag="bkash-config"
  ```

---

## Configuration

The published configuration file (typically `config/bkash.php`) contains the necessary credentials and settings:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | bKash Credentials
    |--------------------------------------------------------------------------
    |
    | Configure your bKash credentials for the tokenized payment system.
    |
    */
    'sandbox' => env('BKASH_SANDBOX', true),

    'credentials' => [
        'app_key'    => env('BKASH_APP_KEY', ''),
        'app_secret' => env('BKASH_APP_SECRET', ''),
        'username'   => env('BKASH_USERNAME', ''),
        'password'   => env('BKASH_PASSWORD', ''),
    ],

    'sandbox_base_url' => env('SANDBOX_BASE_URL', 'https://tokenized.sandbox.bka.sh'),
    'live_base_url'    => env('LIVE_BASE_URL', 'https://tokenized.pay.bka.sh'),

    'version' => 'v1.2.0-beta',

    'cache' => [
        'token_lifetime' => 3600, // Token lifetime in seconds (1 hour)
    ],

    'default_currency' => 'BDT',
    'default_intent'   => 'sale',
    
    // Optional redirect URLs for built-in routes:
    'redirect_urls' => [
        'success' => '/payment/success',
        'failed'  => '/payment/failed',
    ],

    // Option to disable built-in routes if you want to handle routing manually
    'routes' => [
        'enabled' => true,
    ],
];
```

Also, add these environment variables to your **.env** file:

```dotenv
BKASH_SANDBOX=true
BKASH_APP_KEY='0vWQuCRGiUX7EPVjQDr0EUAYtc'
BKASH_APP_SECRET='jcUNPBgbcqEDedNKdvE4G1cAK7D3hCjmJccNPZZBq96QIxxwAMEx'
BKASH_USERNAME='01770618567'
BKASH_PASSWORD='D7DaC<*E*eG'
SANDBOX_BASE_URL=https://tokenized.sandbox.bka.sh
LIVE_BASE_URL=https://tokenized.pay.bka.sh
```

---

## Setup and Publishing Assets

### Migrations

Publish and run the package migrations:

```bash
php artisan vendor:publish --tag="bkash-migrations"
php artisan migrate
```

### Configuration

Publish the configuration file to customize your settings:

```bash
php artisan vendor:publish --tag="bkash-config"
```

### Views and Controllers

If you wish to customize the user interface or override the default controllers:

**Publishing Views:**
  ```bash
  php artisan bkash:setup --publish-views
  ```
  This copies the view files to `resources/views/vendor/bkash/`.

**Publishing Controllers:**
  ```bash
  php artisan bkash:setup --publish-controllers
  ```
  Controllers will be published to `app/Http/Controllers/Vendor/Bkash/`. Update namespaces as needed.

---

## Usage

The package can be used in two ways: using the built-in payment flow or implementing a custom flow manually.

### Creating a Payment

To initiate a payment, use the `createPayment` method in your controller:

```php
use Ihasan\Bkash\Facades\Bkash;

public function initiatePayment(Request $request)
{
    $paymentData = [
        'amount'                  => '100', // Payment amount in BDT
        'payer_reference'         => 'customer123', // Optional parameter
        'callback_url'            => route('bkash.callback'), // If you use this as callback route then package will handle your callback automatically otherwise you have to handle your callback.
        'merchant_invoice_number' => 'INV-123456',
    ];

    try {
        $response = Bkash::createPayment($paymentData);
        // Redirect the user to the bKash payment page
        return redirect()->away($response['bkashURL']);
    } catch (\Exception $e) {
        // Detailed error handling (see Error Handling section)
        return back()->with('error', $e->getMessage());
    }
}
```

### Handling the Callback

After a payment is made, bKash redirects to your callback URL. In your callback controller:

```php
use Ihasan\Bkash\Facades\Bkash;

public function handleCallback(Request $request)
{
    $paymentId = $request->input('paymentID');
    $status    = $request->input('status');

    if ($status === 'success') {
        try {
            $response = Bkash::executePayment($paymentId);
            // Payment executed successfully. Redirect as needed.
            return redirect()->route('payment.success', ['transaction_id' => $response['trxID']]);
        } catch (\Exception $e) {
            return redirect()->route('payment.failed')->with('error', $e->getMessage());
        }
    } else {
        // Handle payment failure or cancellation.
        return redirect()->route('payment.failed')->with('error', 'Payment was not successful');
    }
}
```

### Querying Payment Status

To check the status of a payment:

```php
use Ihasan\Bkash\Facades\Bkash;

public function queryPaymentStatus($paymentId)
{
    try {
        $response = Bkash::queryPayment($paymentId);
        $status   = $response['transactionStatus'];
        return response()->json([
            'success' => $status === 'Completed',
            'message' => 'Payment is ' . $status,
            'data'    => $response,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}
```

### Refunding a Payment

To process a refund (partial or full):

```php
use Ihasan\Bkash\Facades\Bkash;

public function refundPayment(Request $request)
{
    $refundData = [
        'payment_id' => $request->input('payment_id'),
        'trx_id'     => $request->input('trx_id'),
        'amount'     => $request->input('amount'),
        'reason'     => $request->input('reason'),
        // Optionally include 'sku'
    ];

    try {
        $response = Bkash::refundPayment($refundData);
        return response()->json([
            'success' => true,
            'message' => 'Refund processed successfully',
            'data'    => $response,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}
```

### Token Management

For cases when you need to manage tokens manually:

- **Getting a Token:**
  ```php
  $token = Bkash::getToken();
  ```
- **Refreshing a Token:**
  ```php
  $token = Bkash::refreshToken();
  ```

---

## Built-in Payment Flow

The package includes built-in routes and controllers to streamline the payment process. By default, the following routes are registered:

- `GET /bkash/callback` – Handles the bKash callback.
- `GET /bkash/success` – Displays the payment success page.
- `GET /bkash/failed` – Displays the payment failure page.

To use your own routes, disable the built-in ones by setting `'routes' => ['enabled' => false]` in the configuration.

---

## Error Handling

The package is designed with detailed error handling in mind. Dedicated exception classes help you identify and manage specific error cases:

1. **TokenGenerationException:**  
   Thrown when the package fails to generate an authorization token.  
   *Common causes:*  
   - Invalid credentials  
   - Connectivity issues with the bKash API  

   **Example:**
   ```php
   try {
       $token = Bkash::getToken();
   } catch (\Ihasan\Bkash\Exceptions\TokenGenerationException $e) {
       // Log or handle token generation failure
       return back()->with('error', 'Token generation failed: ' . $e->getMessage());
   }
   ```

2. **RefreshTokenException:**  
   Thrown when refreshing the token fails.  
   *Common causes:*  
   - Expired refresh token  
   - API connectivity issues  

   **Example:**
   ```php
   try {
       $token = Bkash::refreshToken();
   } catch (\Ihasan\Bkash\Exceptions\RefreshTokenException $e) {
       // Handle token refresh failure
       return back()->with('error', 'Token refresh failed: ' . $e->getMessage());
   }
   ```

3. **PaymentCreationException:**  
   Thrown if creating a payment fails.  
   *Common causes:*  
   - Invalid amount or parameters  
   - Incorrect callback URL  

   **Example:**
   ```php
   try {
       $response = Bkash::createPayment($paymentData);
   } catch (\Ihasan\Bkash\Exceptions\PaymentCreationException $e) {
       // Handle payment creation failure
       return back()->with('error', 'Payment creation failed: ' . $e->getMessage());
   }
   ```

4. **PaymentExecutionException:**  
   Thrown if executing the payment after user authorization fails.  
   *Common causes:*  
   - Payment already executed or cancelled  
   - Insufficient balance  

   **Example:**
   ```php
   try {
       $response = Bkash::executePayment($paymentId);
   } catch (\Ihasan\Bkash\Exceptions\PaymentExecutionException $e) {
       // Handle payment execution failure
       return redirect()->route('payment.failed')->with('error', 'Payment execution failed: ' . $e->getMessage());
   }
   ```

5. **PaymentQueryException:**  
   Thrown when querying the payment status fails.  
   *Common causes:*  
   - Invalid payment ID  
   - API connectivity issues  

   **Example:**
   ```php
   try {
       $response = Bkash::queryPayment($paymentId);
   } catch (\Ihasan\Bkash\Exceptions\PaymentQueryException $e) {
       // Handle payment query failure
       return response()->json(['error' => 'Payment query failed: ' . $e->getMessage()], 500);
   }
   ```

6. **RefundException:**  
   Thrown if a refund operation fails.  
   *Common causes:*  
   - Payment not found  
   - Refund amount exceeds the payment amount  
   - Payment already refunded  

   **Example:**
   ```php
   try {
       $response = Bkash::refundPayment($refundData);
   } catch (\Ihasan\Bkash\Exceptions\RefundException $e) {
       // Handle refund failure
       return response()->json(['error' => 'Refund failed: ' . $e->getMessage()], 500);
   }
   ```

These detailed error responses allow your application to gracefully handle failures and provide actionable feedback.

---

## Customizing Views and Controllers

To adapt the default UI and controller logic:

- **Publishing Views:**  
  Run:
  ```bash
  php artisan bkash:setup --publish-views
  ```
  This copies view files into your project’s `resources/views/vendor/bkash/` directory for customization.

- **Publishing Controllers:**  
  Run:
  ```bash
  php artisan bkash:setup --publish-controllers
  ```
  Controllers will be copied to `app/Http/Controllers/Vendor/Bkash/`. Adjust namespaces and logic as needed.

---

## Test Credentials

For immediate testing in the sandbox environment, use the following test credentials:

- **Testing Numbers:**  
  - 01929918378  
  - 01619777283  
  - 01619777282  
  - 01823074817
- **OTP:** 123456  
- **PIN:** 12121

Add these credentials to your `.env` file or use them as needed for testing your integration.

---

## Testing

To run the package tests, execute:

```bash
composer test
```

Make sure your testing environment is properly configured according to your Laravel setup.

---

## Contributing

Contributions are welcome! When submitting a pull request, please ensure you:

- Follow PSR-4 coding standards.
- Include tests for new features or bug fixes.
- Update documentation accordingly.

---

## Credits and License

**Credits:**

- Developed by **Md Abul Hassan**
- Special thanks to contributors including Ahmed Shamim Hasan Shaon.

**License:**

This project is licensed under the [MIT License](LICENSE.md).
