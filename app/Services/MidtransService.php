<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransService
{
    protected string $serverKey;
    protected string $baseUrl;
    protected string $snapBaseUrl;
    protected bool $isProduction;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY', '');
        $this->isProduction = (bool) (config('services.midtrans.is_production') ?: env('MIDTRANS_IS_PRODUCTION', false));
        
        $this->baseUrl = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        $this->snapBaseUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    /**
     * Create a virtual account charge request via Midtrans Core API.
     *
     * @param string $orderId
     * @param float $amount
     * @param string $bankCode (bca, bni, bri, mandiri, permata)
     * @return array
     * @throws \Exception
     */
    public function createVirtualAccount(string $orderId, float $amount, string $bankCode): array
    {
        if (empty($this->serverKey)) {
            Log::warning('Midtrans Server Key is not set. Falling back to mock implementation for local environment.');
            return $this->getMockResponse($orderId, $amount, $bankCode);
        }

        $authHeader = 'Basic ' . base64_encode($this->serverKey . ':');
        
        $payload = [
            'payment_type' => '',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
        ];

        // Format payload based on bank
        if ($bankCode === 'mandiri') {
            $payload['payment_type'] = 'echannel';
            $payload['echannel'] = [
                'bill_info1' => 'Pembayaran Toko',
                'bill_info2' => 'Order ' . $orderId,
            ];
        } elseif ($bankCode === 'permata') {
            $payload['payment_type'] = 'permata';
        } else {
            // bca, bni, bri
            $payload['payment_type'] = 'bank_transfer';
            $payload['bank_transfer'] = [
                'bank' => $bankCode,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/charge', $payload);

            if (!$response->successful()) {
                Log::error('Midtrans Charge Failed', [
                    'order_id' => $orderId,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Midtrans API error: ' . $response->json('status_message', 'Unknown error'));
            }

            $data = $response->json();
            
            $vaNumber = null;
            $billerCode = null;

            if ($bankCode === 'mandiri') {
                $vaNumber = $data['bill_key'] ?? null;
                $billerCode = $data['biller_code'] ?? null;
            } elseif ($bankCode === 'permata') {
                $vaNumber = $data['permata_va_number'] ?? null;
            } else {
                $vaNumber = $data['va_numbers'][0]['va_number'] ?? null;
            }

            return [
                'transaction_id' => $data['transaction_id'] ?? null,
                'va_number' => $vaNumber,
                'bill_key' => $data['bill_key'] ?? null,
                'biller_code' => $billerCode,
                'transaction_status' => $data['transaction_status'] ?? 'pending',
                'raw' => $data
            ];

        } catch (\Exception $e) {
            Log::error('Exception during Midtrans charge call', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            
            // If local environment, fall back to mock response to ensure smooth testing
            if (app()->environment('local', 'testing')) {
                return $this->getMockResponse($orderId, $amount, $bankCode);
            }

            throw $e;
        }
    }

    /**
     * Generate mock payment details for testing / local environment without Midtrans Key.
     */
    protected function getMockResponse(string $orderId, float $amount, string $bankCode): array
    {
        $mockVa = '88012' . rand(10000000, 99999999);
        $transactionId = 'mock-midtrans-id-' . uniqid();
        
        $billerCode = null;
        if ($bankCode === 'mandiri') {
            $billerCode = '70012';
        }

        return [
            'transaction_id' => $transactionId,
            'va_number' => $mockVa,
            'bill_key' => $bankCode === 'mandiri' ? $mockVa : null,
            'biller_code' => $billerCode,
            'transaction_status' => 'pending',
            'raw' => [
                'mock' => true,
                'message' => 'Simulated local Midtrans response'
            ]
        ];
    }

    /**
     * Create Midtrans Snap transaction token and redirect URL.
     */
    public function createSnapTransaction(string $orderId, float $amount, ?string $bankCode = null): array
    {
        if (empty($this->serverKey)) {
            Log::warning('Midtrans Server Key is not set. Falling back to mock Snap response.');
            return $this->getMockSnapResponse($orderId, $amount, $bankCode);
        }

        $authHeader = 'Basic ' . base64_encode($this->serverKey . ':');

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
        ];

        if ($bankCode) {
            $payload['enabled_payments'] = [$this->getPaymentMethodFromBankCode($bankCode)];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($this->snapBaseUrl . '/transactions', $payload);

            if (!$response->successful()) {
                Log::error('Midtrans Snap Transaction Failed', [
                    'order_id' => $orderId,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Midtrans API error: ' . $response->json('error_messages.0', 'Unknown Snap error'));
            }

            $data = $response->json();

            return [
                'snap_token' => $data['token'] ?? null,
                'snap_url' => $data['redirect_url'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? ('SNAP-' . strtoupper(Str::random(12))),
                'raw' => $data
            ];

        } catch (\Exception $e) {
            Log::error('Exception during Midtrans Snap transaction call', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            if (app()->environment('local', 'testing')) {
                return $this->getMockSnapResponse($orderId, $amount, $bankCode);
            }

            throw $e;
        }
    }

    /**
     * Get rill transaction status from Midtrans.
     */
    public function getTransactionStatus(string $orderId): array
    {
        if (empty($this->serverKey)) {
            Log::warning('Midtrans Server Key is not set. Falling back to mock transaction status.');
            return [
                'status' => 'settlement',
                'payment_type' => 'bank_transfer',
                'va_number' => '1234567890',
                'bank' => 'bca',
                'raw' => ['mock' => true]
            ];
        }

        $authHeader = 'Basic ' . base64_encode($this->serverKey . ':');

        try {
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/' . $orderId . '/status');

            if (!$response->successful()) {
                throw new \Exception('Midtrans API status error: ' . $response->json('status_message', 'Unknown error'));
            }

            $data = $response->json();
            $status = $data['transaction_status'] ?? 'pending';

            $vaNumber = null;
            $bank = null;
            $billerCode = null;

            if (($data['payment_type'] ?? '') === 'bank_transfer' && !empty($data['va_numbers'])) {
                $bank = $data['va_numbers'][0]['bank'] ?? null;
                $vaNumber = $data['va_numbers'][0]['va_number'] ?? null;
            } elseif (($data['payment_type'] ?? '') === 'echannel') {
                $bank = 'mandiri';
                $vaNumber = $data['bill_key'] ?? null;
                $billerCode = $data['biller_code'] ?? null;
            } elseif (($data['payment_type'] ?? '') === 'cstore') {
                $bank = $data['store'] ?? 'cstore';
                $vaNumber = $data['payment_code'] ?? null;
            }

            return [
                'status' => $status,
                'payment_type' => $data['payment_type'] ?? null,
                'bank' => $bank,
                'va_number' => $vaNumber,
                'biller_code' => $billerCode,
                'raw' => $data
            ];
        } catch (\Exception $e) {
            Log::error('Error getting Midtrans transaction status', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            if (app()->environment('local', 'testing')) {
                return [
                    'status' => 'settlement',
                    'payment_type' => 'bank_transfer',
                    'va_number' => '1234567890',
                    'bank' => 'bca',
                    'raw' => ['mock' => true]
                ];
            }

            throw $e;
        }
    }

    /**
     * Generate mock Snap details for local testing.
     */
    protected function getMockSnapResponse(string $orderId, float $amount, ?string $bankCode = null): array
    {
        $mockToken = 'mock-snap-token-' . Str::random(20);
        $mockUrl = 'https://app.sandbox.midtrans.com/snap/v3/redirection/' . $mockToken;
        $transactionId = 'mock-midtrans-id-' . uniqid();

        return [
            'snap_token' => $mockToken,
            'snap_url' => $mockUrl,
            'transaction_id' => $transactionId,
            'raw' => [
                'mock' => true,
                'message' => 'Simulated local Midtrans Snap response'
            ]
        ];
    }

    /**
     * Helper to map bank_code to Midtrans Snap payment method filter.
     */
    protected function getPaymentMethodFromBankCode(string $bankCode): string
    {
        switch ($bankCode) {
            case 'bca': return 'bca_va';
            case 'bni': return 'bni_va';
            case 'bri': return 'bri_va';
            case 'mandiri': return 'mandiri_va';
            case 'permata': return 'permata_va';
            default: return 'credit_card';
        }
    }
}
