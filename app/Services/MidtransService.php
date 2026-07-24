<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransService
{
    protected string $serverKey;
    protected string $clientKey;
    protected string $baseUrl;
    protected string $snapBaseUrl;
    protected bool   $isProduction;

    public function __construct()
    {
        $this->serverKey   = (string) (config('services.midtrans.server_key') ?? env('MIDTRANS_SERVER_KEY') ?? '');
        $this->clientKey   = (string) (config('services.midtrans.client_key') ?? env('MIDTRANS_CLIENT_KEY') ?? '');
        $this->isProduction = filter_var(
            config('services.midtrans.is_production') ?? env('MIDTRANS_IS_PRODUCTION') ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        $this->baseUrl = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        $this->snapBaseUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    /**
     * Create a Midtrans Snap transaction token and redirect URL.
     *
     * @param  string      $orderId
     * @param  float       $amount
     * @param  string|null $bankCode        Optional bank filter for Snap payment method
     * @param  array       $customerDetails ['first_name', 'last_name', 'email', 'phone']
     * @param  array       $itemDetails     [['id', 'price', 'quantity', 'name'], ...]
     * @return array{snap_token: string|null, snap_url: string|null, transaction_id: string|null, raw: array}
     * @throws \Exception
     */
    public function createSnapTransaction(
        string $orderId,
        float $amount,
        ?string $bankCode = null,
        array $customerDetails = [],
        array $itemDetails = []
    ): array {
        if (empty($this->serverKey)) {
            Log::warning('Midtrans Server Key is not set. Falling back to mock Snap response.', [
                'order_id' => $orderId,
            ]);
            return $this->getMockSnapResponse($orderId, $amount, $bankCode);
        }

        $authHeader = 'Basic ' . base64_encode($this->serverKey . ':');

        $payload = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $amount,
            ],
        ];

        // Attach customer details when provided
        if (!empty($customerDetails)) {
            $payload['customer_details'] = array_filter([
                'first_name' => $customerDetails['first_name'] ?? null,
                'last_name'  => $customerDetails['last_name']  ?? null,
                'email'      => $customerDetails['email']       ?? null,
                'phone'      => $customerDetails['phone']       ?? null,
            ]);
        }

        // Attach item details when provided
        if (!empty($itemDetails)) {
            $payload['item_details'] = $itemDetails;
        }

        // Filter payment method to specific bank VA if specified
        if ($bankCode) {
            $payload['enabled_payments'] = [$this->getPaymentMethodFromBankCode($bankCode)];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ])
                ->timeout(30)
                ->post($this->snapBaseUrl . '/transactions', $payload);

            Log::info('Midtrans Snap Request', [
                'order_id' => $orderId,
                'status'   => $response->status(),
            ]);

            if (!$response->successful()) {
                $errMessages = $response->json('error_messages', []);
                $errMessage  = is_array($errMessages) && !empty($errMessages)
                    ? implode(', ', $errMessages)
                    : 'Unknown Snap error';

                Log::error('Midtrans Snap Transaction Failed', [
                    'order_id' => $orderId,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);

                throw new \Exception('Midtrans Snap error: ' . $errMessage);
            }

            $data = $response->json();

            return [
                'snap_token'     => $data['token']        ?? null,
                'snap_url'       => $data['redirect_url'] ?? null,
                'transaction_id' => 'SNAP-' . strtoupper(Str::random(12)),
                'raw'            => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Exception during Midtrans Snap transaction call', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);

            // Fallback to mock in local / testing environments
            if (app()->environment('local', 'testing')) {
                return $this->getMockSnapResponse($orderId, $amount, $bankCode);
            }

            throw $e;
        }
    }

    /**
     * Create a virtual account charge via Midtrans Core API.
     *
     * @param  string $orderId
     * @param  float  $amount
     * @param  string $bankCode  bca | bni | bri | mandiri | permata
     * @return array{transaction_id: string|null, va_number: string|null, bill_key: string|null, biller_code: string|null, transaction_status: string, raw: array}
     * @throws \Exception
     */
    public function createVirtualAccount(string $orderId, float $amount, string $bankCode): array
    {
        if (empty($this->serverKey)) {
            Log::warning('Midtrans Server Key is not set. Falling back to mock VA response.', [
                'order_id'  => $orderId,
                'bank_code' => $bankCode,
            ]);
            return $this->getMockVAResponse($orderId, $amount, $bankCode);
        }

        $authHeader = 'Basic ' . base64_encode($this->serverKey . ':');

        $payload = [
            'payment_type'        => '',
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $amount,
            ],
        ];

        if ($bankCode === 'mandiri') {
            $payload['payment_type'] = 'echannel';
            $payload['echannel']     = [
                'bill_info1' => 'Pembayaran',
                'bill_info2' => 'Order ' . $orderId,
            ];
        } elseif ($bankCode === 'permata') {
            $payload['payment_type'] = 'permata';
        } else {
            $payload['payment_type']  = 'bank_transfer';
            $payload['bank_transfer'] = ['bank' => $bankCode];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ])
                ->timeout(30)
                ->post($this->baseUrl . '/charge', $payload);

            if (!$response->successful()) {
                Log::error('Midtrans Charge Failed', [
                    'order_id' => $orderId,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);
                throw new \Exception(
                    'Midtrans API error: ' . $response->json('status_message', 'Unknown error')
                );
            }

            $data       = $response->json();
            $vaNumber   = null;
            $billerCode = null;

            if ($bankCode === 'mandiri') {
                $vaNumber   = $data['bill_key']    ?? null;
                $billerCode = $data['biller_code'] ?? null;
            } elseif ($bankCode === 'permata') {
                $vaNumber = $data['permata_va_number'] ?? null;
            } else {
                $vaNumber = $data['va_numbers'][0]['va_number'] ?? null;
            }

            return [
                'transaction_id'     => $data['transaction_id']     ?? null,
                'va_number'          => $vaNumber,
                'bill_key'           => $data['bill_key']            ?? null,
                'biller_code'        => $billerCode,
                'transaction_status' => $data['transaction_status']  ?? 'pending',
                'raw'                => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Exception during Midtrans charge call', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);

            if (app()->environment('local', 'testing')) {
                return $this->getMockVAResponse($orderId, $amount, $bankCode);
            }

            throw $e;
        }
    }

    /**
     * Get real transaction status from Midtrans Core API.
     *
     * @param  string $orderId
     * @return array{status: string, payment_type: string|null, bank: string|null, va_number: string|null, biller_code: string|null, raw: array}
     * @throws \Exception
     */
    public function getTransactionStatus(string $orderId): array
    {
        if (empty($this->serverKey)) {
            Log::warning('Midtrans Server Key not set. Returning mock status.', ['order_id' => $orderId]);
            return $this->getMockTransactionStatus();
        }

        $authHeader = 'Basic ' . base64_encode($this->serverKey . ':');

        try {
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Accept'        => 'application/json',
            ])
                ->timeout(15)
                ->get($this->baseUrl . '/' . $orderId . '/status');

            if (!$response->successful()) {
                throw new \Exception(
                    'Midtrans status error: ' . $response->json('status_message', 'Unknown error')
                );
            }

            $data        = $response->json();
            $status      = $data['transaction_status'] ?? 'pending';
            $paymentType = $data['payment_type']       ?? '';
            $vaNumber    = null;
            $bank        = null;
            $billerCode  = null;

            if ($paymentType === 'bank_transfer' && !empty($data['va_numbers'])) {
                $bank     = $data['va_numbers'][0]['bank']      ?? null;
                $vaNumber = $data['va_numbers'][0]['va_number'] ?? null;
            } elseif ($paymentType === 'echannel') {
                $bank       = 'mandiri';
                $vaNumber   = $data['bill_key']    ?? null;
                $billerCode = $data['biller_code'] ?? null;
            } elseif ($paymentType === 'cstore') {
                $bank     = $data['store']        ?? 'cstore';
                $vaNumber = $data['payment_code'] ?? null;
            } elseif ($paymentType === 'qris' || $paymentType === 'gopay') {
                $bank     = $paymentType;
                $vaNumber = $data['acquirer'] ?? null;
            }

            return [
                'status'       => $status,
                'payment_type' => $paymentType ?: null,
                'bank'         => $bank,
                'va_number'    => $vaNumber,
                'biller_code'  => $billerCode,
                'raw'          => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting Midtrans transaction status', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);

            if (app()->environment('local', 'testing')) {
                return $this->getMockTransactionStatus();
            }

            throw $e;
        }
    }

    /**
     * Verify the webhook notification signature from Midtrans.
     *
     * @param  string $orderId
     * @param  string $statusCode
     * @param  string $grossAmount       Raw string exactly as sent by Midtrans (e.g. "10000.00")
     * @param  string $receivedSignature
     * @return bool
     */
    public function verifyNotificationSignature(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $receivedSignature
    ): bool {
        if (empty($this->serverKey)) {
            return false;
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        return hash_equals($expected, $receivedSignature);
    }

    /**
     * Return the client key (for use in frontend Snap.js / Midtrans JS).
     */
    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    /**
     * Return whether the service is in production mode.
     */
    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    // └└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└
    // Mock / Fallback Responses (local & testing environments)
    // └└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└

    /** Generate mock Snap transaction details for local testing. */
    protected function getMockSnapResponse(string $orderId, float $amount, ?string $bankCode = null): array
    {
        $mockToken     = 'mock-snap-token-' . Str::random(20);
        $mockUrl       = 'https://app.sandbox.midtrans.com/snap/v3/redirection/' . $mockToken;
        $transactionId = 'mock-snap-id-' . uniqid();

        return [
            'snap_token'     => $mockToken,
            'snap_url'       => $mockUrl,
            'transaction_id' => $transactionId,
            'raw'            => [
                'mock'    => true,
                'message' => 'Simulated local Midtrans Snap response',
            ],
        ];
    }

    /** Generate mock virtual-account payment details for local testing. */
    protected function getMockVAResponse(string $orderId, float $amount, string $bankCode): array
    {
        $mockVa        = '88012' . rand(10000000, 99999999);
        $transactionId = 'mock-va-id-' . uniqid();
        $billerCode    = $bankCode === 'mandiri' ? '70012' : null;

        return [
            'transaction_id'     => $transactionId,
            'va_number'          => $mockVa,
            'bill_key'           => $bankCode === 'mandiri' ? $mockVa : null,
            'biller_code'        => $billerCode,
            'transaction_status' => 'pending',
            'raw'                => [
                'mock'    => true,
                'message' => 'Simulated local Midtrans VA response',
            ],
        ];
    }

    /** Return a mock transaction status when server key is absent. */
    protected function getMockTransactionStatus(): array
    {
        return [
            'status'       => 'settlement',
            'payment_type' => 'bank_transfer',
            'bank'         => 'bca',
            'va_number'    => '1234567890',
            'biller_code'  => null,
            'raw'          => ['mock' => true],
        ];
    }

    // └└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└
    // Helpers
    // └└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└└

    /** Map a bank / payment code to a Midtrans Snap payment method identifier. */
    protected function getPaymentMethodFromBankCode(string $bankCode): string
    {
        return match ($bankCode) {
            'bca'     => 'bca_va',
            'bni'     => 'bni_va',
            'bri'     => 'bri_va',
            'mandiri' => 'echannel',
            'permata' => 'permata_va',
            'gopay'   => 'gopay',
            'qris'    => 'qris',
            default   => 'bank_transfer',
        };
    }
}
