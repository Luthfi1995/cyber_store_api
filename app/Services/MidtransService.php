<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected string $serverKey;
    protected string $baseUrl;
    protected bool $isProduction;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY', '');
        $this->isProduction = (bool) (config('services.midtrans.is_production') ?: env('MIDTRANS_IS_PRODUCTION', false));
        
        $this->baseUrl = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
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
}
