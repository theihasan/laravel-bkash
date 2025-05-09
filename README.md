![Free Palestine](https://github.com/user-attachments/assets/2b796609-c819-4cf6-b454-993e47a6e0f2)

# Laravel bKash

A simple Laravel package for integrating the bKash Tokenized Payment Gateway into your application. With built-in payment flow and full control via manual methods, this package supports payment creation, execution, status queries, refunds, and token management.

---

## Table of Contents

1. [Overview](#overview)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Usage](#usage)
    - [Initiating a Payment](#initiating-a-payment)
    - [Handling the Callback](#handling-the-callback)
    - [Querying Payment Status](#querying-payment-status)
    - [Processing a Refund](#processing-a-refund)
    - [Managing Tokens](#managing-tokens)
6. [Built-in Payment Flow](#built-in-payment-flow)
7. [Error Handling](#error-handling)
8. [Customization](#customization)
9. [Test Credentials](#test-credentials)
10. [Testing](#testing)
11. [Contributing](#contributing)
12. [Credits and License](#credits-and-license)

---

## Overview

The **Laravel bKash** package simplifies integrating bKash’s tokenized payment gateway into your Laravel projects. It provides:
- Quick installation and configuration.
- Built-in controllers, routes, and views for out-of-the-box payment flow.
- Manual methods for complete control.
- Detailed error handling through custom exceptions.

---

## Requirements

- **PHP:** 8.0 or higher  
- **Laravel:** 8.x or later  
- **cURL Extension:** Enabled

---

## Installation

Install the package via Composer:

```bash
composer require theihasan/laravel-bkash
```

Then, run the setup command to test the connection and publish assets:

```bash
php artisan bkash:setup --test --publish-views --publish-controllers
```

Alternatively, publish individual assets as needed:

- **Migrations:**  
  ```bash
  php artisan vendor:publish --tag="bkash-migrations"
  php artisan migrate
  ```
- **Configuration:**  
  ```bash
  php artisan vendor:publish --tag="bkash-config"
  ```

---

## Configuration

After publishing, update the `config/bkash.php` file with your bKash credentials and settings:

```php
return [
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
        'token_lifetime' => 3600,
    ],

    'default_currency' => 'BDT',
    'default_intent'   => 'sale',

    'redirect_urls' => [
        'success' => '/payment/success',
        'failed'  => '/payment/failed',
    ],

    'routes' => [
        'enabled' => true,
    ],
];
```

Also, add the necessary variables to your **.env** file:

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

## Usage

You can use the package with its built-in payment flow or build a custom process.

### Initiating a Payment

Use the provided `createPayment` method to start a payment:

```php
use Ihasan\Bkash\Facades\Bkash;

public function initiatePayment(Request $request)
{
    $paymentData = [
        'amount'                  => '100', // Payment amount in BDT
        'payer_reference'         => 'customer123',
        'callback_url'            => route('bkash.callback'), //If you use this built in route then this package will handle your callback automatically otherwise you have to implement your own callback logic. So don't change this to use automatic callback handling
        'merchant_invoice_number' => 'INV-123456',
    ];

    try {
        $response = Bkash::createPayment($paymentData);
        // Redirect to the bKash payment page
        return redirect()->away($response['bkashURL']);
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}
```

### Handling the Callback

After payment, bKash will redirect to your callback URL:

```php
use Ihasan\Bkash\Facades\Bkash;

public function handleCallback(Request $request)
{
    if ($request->input('status') === 'success') {
        try {
            $response = Bkash::executePayment($request->input('paymentID'));
            return redirect()->route('payment.success', ['transaction_id' => $response['trxID']]);
        } catch (\Exception $e) {
            return redirect()->route('payment.failed')->with('error', $e->getMessage());
        }
    }
    return redirect()->route('payment.failed')->with('error', 'Payment was not successful');
}
```

### Querying Payment Status

Check a payment’s status using:

```php
use Ihasan\Bkash\Facades\Bkash;

public function queryPaymentStatus($paymentId)
{
    try {
        $response = Bkash::queryPayment($paymentId);
        $status = $response['transactionStatus'];
        return response()->json([
            'success' => $status === 'Completed',
            'message' => 'Payment is ' . $status,
            'data'    => $response,
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
```

### Processing a Refund

Initiate a refund (partial or full) with:

```php
use Ihasan\Bkash\Facades\Bkash;

public function refundPayment(Request $request)
{
    $refundData = [
        'payment_id' => $request->input('payment_id'),
        'trx_id'     => $request->input('trx_id'),
        'amount'     => $request->input('amount'),
        'reason'     => $request->input('reason'),
    ];

    try {
        $response = Bkash::refundPayment($refundData);
        return response()->json(['success' => true, 'message' => 'Refund processed successfully', 'data' => $response]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
```

### Managing Tokens

For manual token operations:

```php
// Get a token
$token = Bkash::getToken();

// Refresh a token
$token = Bkash::refreshToken();
```

---

## Built-in Payment Flow

By default, the package registers these routes:
- **GET /bkash/callback** – Payment callback handling.
- **GET /bkash/success** – Payment success page.
- **GET /bkash/failed** – Payment failure page.

To define your own routes, simply disable the built-in ones in `config/bkash.php` by setting:

```php
'routes' => [
    'enabled' => false,
],
```

---

## Error Handling

The package provides clear exception classes to help you handle errors:
- **TokenGenerationException:** When token generation fails.
- **RefreshTokenException:** When token refresh fails.
- **PaymentCreationException:** When payment creation fails.
- **PaymentExecutionException:** When executing payment fails.
- **PaymentQueryException:** When payment status query fails.
- **RefundException:** When refund processing fails.

Handle exceptions as shown in the usage examples above.

---

## Customization

Customize the built-in views and controllers to match your needs:
- **Views:**  
  ```bash
  php artisan bkash:setup --publish-views
  ```  
  Files will be copied to `resources/views/vendor/bkash/`.
  
- **Controllers:**  
  ```bash
  php artisan bkash:setup --publish-controllers
  ```  
  Controllers will appear in `app/Http/Controllers/Vendor/Bkash/`. Adjust namespaces as needed.

---

## Database Configuration

Starting from version 1.1.0, you can customize the database table prefix used by the package. This is useful when you want to avoid table name conflicts or organize your database schema.

### Setting a Custom Table Prefix

By default, all tables created by this package use the `bkash_` prefix. You can change this by updating your `.env` file:

```dotenv
BKASH_TABLE_PREFIX=custom_prefix_
```

Or by directly modifying the `config/bkash.php` file:

```php
'database' => [
    'table_prefix' => env('BKASH_TABLE_PREFIX', 'bkash_'),
],
```

### For Existing Installations

If you're updating from a previous version and want to use a custom table prefix:

1. Publish the new migration file:
   ```bash
   php artisan vendor:publish --tag="bkash-migrations"
   ```

2. Set your desired prefix in the `.env` file or config file.

3. Run the migration to create new tables with your prefix and migrate existing data:
   ```bash
   php artisan migrate
   ```

> **Note:** The migration will automatically copy your existing data to the new tables with your custom prefix. Your original tables will remain untouched, so you can verify the data before removing the old tables if needed.

### Important Considerations

- Changing the table prefix after you've already been using the package will create new tables with the new prefix.
- The package will automatically use the tables with the configured prefix.
- If you're using direct database queries in your application that reference these tables, make sure to update those queries to use the new table names.

---

## Test Credentials

For sandbox testing, you may use these credentials (or update your **.env** accordingly):
- **Testing Numbers:**  
  - 01929918378  
  - 01619777283  
  - 01619777282  
  - 01823074817
- **OTP:** 123456  
- **PIN:** 12121

---

## Testing

Run package tests with:

```bash
composer test
```

Ensure your testing environment is set up as required by your Laravel configuration.

---

## Contributing

Contributions are welcome. When submitting a pull request:
- Follow PSR-4 coding standards.
- Include tests for new features or bug fixes.
- Update the documentation as needed.

---

## Credits and License

**Credits:**

- Developed by [Abul Hassan](https://github.com/theihasan)
- Special thanks to:
  - [Ahmed Shamim Hassan Shaon](https://github.com/me-shaon) for his invaluable guidance in package development.
  - [Anis Uddin Ahmed](https://github.com/ajaxray) for his valuable insights and support.

**License:**  
Licensed under the [MIT License](LICENSE.md).

---
