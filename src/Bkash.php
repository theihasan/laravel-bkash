<?php

namespace Ihasan\Bkash;

use Ihasan\Bkash\Exceptions\PaymentCreateException;
use Ihasan\Bkash\Exceptions\PaymentExecuteException;
use Ihasan\Bkash\Exceptions\RefreshTokenException;
use Ihasan\Bkash\Exceptions\TokenGenerationException;
use Ihasan\Bkash\Models\BkashPayment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Bkash
{
    protected string $baseUrl;

    protected array $credentials;

    protected string $version;

    public function __construct()
    {
        $this->baseUrl = config('bkash.sandbox')
            ? config('bkash.sandbox_base_url')
            : config('bkash.live_base_url');

        $this->credentials = config('bkash.credentials');
        $this->version = config('bkash.version');
    }

    /**
     * Get the authorization token
     *
     * @throws TokenGenerationException
     */
    public function getToken(): string
    {
        $cacheKey = 'bkash_token';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withHeaders([
                'username' => $this->credentials['username'],
                'password' => $this->credentials['password'],
            ])->post("{$this->baseUrl}/{$this->version}/tokenized/checkout/token/grant", [
                'app_key' => $this->credentials['app_key'],
                'app_secret' => $this->credentials['app_secret'],
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id_token'])) {
                $token = $data['id_token'];
                $expiresIn = $data['expires_in'] ?? config('bkash.cache.token_lifetime');

                Cache::put($cacheKey, $token, now()->addSeconds($expiresIn));

                if (isset($data['refresh_token'])) {
                    Cache::put('bkash_refresh_token', $data['refresh_token'], now()->addDays(30));
                }

                return $token;
            }

            throw new TokenGenerationException(
                $data['statusMessage'] ?? 'Failed to generate token',
                $data['statusCode'] ?? 500
            );
        } catch (\Exception $e) {
            if ($e instanceof TokenGenerationException) {
                throw $e;
            }

            throw new TokenGenerationException('Failed to connect to bKash API: '.$e->getMessage());
        }
    }

    /**
     * Refresh the authorization token
     *
     * @throws RefreshTokenException
     */
    public function refreshToken(): string
    {
        $refreshToken = Cache::get('bkash_refresh_token');

        if (! $refreshToken) {
            return $this->getToken();
        }

        try {
            $response = Http::withHeaders([
                'username' => $this->credentials['username'],
                'password' => $this->credentials['password'],
            ])->post("{$this->baseUrl}/{$this->version}/tokenized/checkout/token/refresh", [
                'app_key' => $this->credentials['app_key'],
                'app_secret' => $this->credentials['app_secret'],
                'refresh_token' => $refreshToken,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id_token'])) {
                $token = $data['id_token'];
                $expiresIn = $data['expires_in'] ?? config('bkash.cache.token_lifetime');

                Cache::put('bkash_token', $token, now()->addSeconds($expiresIn));

                if (isset($data['refresh_token'])) {
                    Cache::put('bkash_refresh_token', $data['refresh_token'], now()->addDays(30));
                }

                return $token;
            }

            throw new RefreshTokenException(
                $data['statusMessage'] ?? 'Failed to refresh token',
                $data['statusCode'] ?? 500
            );
        } catch (\Exception $e) {
            if ($e instanceof RefreshTokenException) {
                throw $e;
            }

            throw new RefreshTokenException('Failed to connect to bKash API: '.$e->getMessage());
        }
    }

    /**
     * Create a payment
     *
     * @throws PaymentCreateException
     */
    public function createPayment(array $data): array
    {
        try {
            $token = $this->getToken();

            $payload = [
                'mode' => '0011',
                'payerReference' => $data['payer_reference'] ?? null,
                'callbackURL' => $data['callback_url'],
                'amount' => (string) $data['amount'],
                'currency' => $data['currency'] ?? config('bkash.default_currency'),
                'intent' => $data['intent'] ?? config('bkash.default_intent'),
                'merchantInvoiceNumber' => $data['merchant_invoice_number'],
            ];

            if (isset($data['agreement_id'])) {
                $payload['agreementID'] = $data['agreement_id'];
            }

            $response = Http::withHeaders([
                'Authorization' => $token,
                'X-APP-Key' => $this->credentials['app_key'],
            ])->post("{$this->baseUrl}/{$this->version}/tokenized/checkout/create", $payload);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['paymentID'])) {
                BkashPayment::create([
                    'payment_id' => $responseData['paymentID'],
                    'agreement_id' => $responseData['agreementID'] ?? null,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? config('bkash.default_currency'),
                    'intent' => $data['intent'] ?? config('bkash.default_intent'),
                    'merchant_invoice_number' => $data['merchant_invoice_number'],
                    'transaction_status' => $responseData['transactionStatus'],
                    'payment_create_time' => now(),
                    'status_code' => $responseData['statusCode'],
                    'status_message' => $responseData['statusMessage'],
                ]);

                return $responseData;
            }

            throw new PaymentCreateException(
                $responseData['statusMessage'] ?? 'Failed to create payment',
                $responseData['statusCode'] ?? 500
            );
        } catch (\Exception $e) {
            if ($e instanceof PaymentCreateException) {
                throw $e;
            }

            throw new PaymentCreateException('Failed to create payment: '.$e->getMessage());
        }
    }

    /**
     * Execute a payment
     *
     * @throws PaymentExecuteException
     */
    public function executePayment(string $paymentId): array
    {
        try {
            $token = $this->getToken();

            $response = Http::withHeaders([
                'Authorization' => $token,
                'X-APP-Key' => $this->credentials['app_key'],
            ])->post("{$this->baseUrl}/{$this->version}/tokenized/checkout/execute", [
                'paymentID' => $paymentId,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['trxID'])) {

                $payment = BkashPayment::where('payment_id', $paymentId)->first();

                if ($payment) {
                    $payment->update([
                        'trx_id' => $data['trxID'],
                        'customer_msisdn' => $data['customerMsisdn'] ?? null,
                        'payer_reference' => $data['payerReference'] ?? null,
                        'agreement_id' => $data['agreementID'] ?? null,
                        'payment_execute_time' => now(),
                        'agreement_execute_time' => isset($data['agreementExecuteTime']) ? now() : null,
                        'agreement_status' => $data['agreementStatus'] ?? null,
                        'transaction_status' => $data['transactionStatus'],
                        'status_code' => $data['statusCode'],
                        'status_message' => $data['statusMessage'],
                    ]);
                }

                return $data;
            }

            throw new PaymentExecuteException(
                $data['statusMessage'] ?? 'Failed to execute payment',
                $data['statusCode'] ?? 500
            );
        } catch (\Exception $e) {
            if ($e instanceof PaymentExecuteException) {
                throw $e;
            }

            throw new PaymentExecuteException('Failed to execute payment: '.$e->getMessage());
        }
    }

    /**
     * Query payment status
     *
     * @param string $paymentId
     * @return array
     * @throws PaymentQueryException
     */
    public function queryPayment(string $paymentId): array
    {
        try {
            $token = $this->getToken();

            $response = Http::withHeaders([
                'Authorization' => $token,
                'X-APP-Key' => $this->credentials['app_key'],
            ])->post("{$this->baseUrl}/{$this->version}/tokenized/checkout/payment/status", [
                'paymentID' => $paymentId,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['paymentID'])) {
                // Update the payment information if it exists
                $payment = BkashPayment::where('payment_id', $paymentId)->first();

                if ($payment) {
                    $payment->update([
                        'trx_id' => $data['trxID'] ?? $payment->trx_id,
                        'customer_msisdn' => $data['customerMsisdn'] ?? $payment->customer_msisdn,
                        'payer_reference' => $data['payerReference'] ?? $payment->payer_reference,
                        'agreement_id' => $data['agreementID'] ?? $payment->agreement_id,
                        'transaction_status' => $data['transactionStatus'],
                        'status_code' => $data['statusCode'],
                        'status_message' => $data['statusMessage'],
                    ]);
                }

                return $data;
            }

            throw new PaymentQueryException(
                $data['statusMessage'] ?? 'Failed to query payment status',
                $data['statusCode'] ?? 500
            );
        } catch (\Exception $e) {
            if ($e instanceof PaymentQueryException) {
                throw $e;
            }

            throw new PaymentQueryException('Failed to query payment status: ' . $e->getMessage());
        }
    }

    /**
     * Refund a payment
     *
     * @param array $data
     * @return array
     * @throws RefundException
     */
    public function refundPayment(array $data): array
    {
        try {
            $token = $this->getToken();

            $payload = [
                'paymentID' => $data['payment_id'],
                'trxID' => $data['trx_id'],
                'amount' => (string) $data['amount'],
                'sku' => $data['sku'] ?? null,
                'reason' => $data['reason'] ?? 'Refund requested by customer',
            ];

            $response = Http::withHeaders([
                'Authorization' => $token,
                'X-APP-Key' => $this->credentials['app_key'],
            ])->post("{$this->baseUrl}/{$this->version}/tokenized/checkout/payment/refund", $payload);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['refundTrxID'])) {

                $refund = BkashRefund::create([
                    'payment_id' => $data['payment_id'],
                    'original_trx_id' => $responseData['originalTrxID'],
                    'refund_trx_id' => $responseData['refundTrxID'],
                    'amount' => $data['amount'],
                    'currency' => $responseData['currency'] ?? 'BDT',
                    'transaction_status' => $responseData['transactionStatus'],
                    'completed_time' => $responseData['completedTime'] ?? now(),
                    'reason' => $data['reason'] ?? 'Refund requested by customer',
                ]);

                $payment = BkashPayment::where('payment_id', $data['payment_id'])->first();
                if ($payment) {
                    $payment->update([
                        'transaction_status' => 'REFUNDED',
                    ]);
                }

                return $responseData;
            }

            throw new RefundException(
                $responseData['statusMessage'] ?? 'Failed to refund payment',
                $responseData['statusCode'] ?? 500
            );
        } catch (\Exception $e) {
            if ($e instanceof RefundException) {
                throw $e;
            }

            throw new RefundException('Failed to refund payment: ' . $e->getMessage());
        }
    }

}
