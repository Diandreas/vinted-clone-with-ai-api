<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotchPayService
{
    private string $baseUrl;
    private ?string $publicKey;
    private ?string $secretKey;
    private bool $sandbox;

    public function __construct()
    {
        $this->baseUrl = config('services.notchpay.base_url', 'https://api.notchpay.co');
        $this->publicKey = config('services.notchpay.public_key');
        $this->secretKey = config('services.notchpay.secret_key');
        $this->sandbox = config('services.notchpay.sandbox', true);
    }

    private function headers(): array
    {
        return [
            'Authorization' => $this->publicKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Cache-Control' => 'no-cache',
        ];
    }

    public function initializePayment(array $payload): array
    {
        if (empty($this->publicKey) || empty($this->secretKey)) {
            throw new \InvalidArgumentException('NotchPay credentials are not configured. Please set NOTCHPAY_PUBLIC_KEY and NOTCHPAY_SECRET_KEY in your .env file.');
        }
        
        $this->validatePayload($payload);
        
        $response = Http::withHeaders($this->headers())
            ->post(rtrim($this->baseUrl, '/') . '/payments/initialize', $payload);
            
        return $this->responseToArray($response);
    }

    public function verifyPayment(string $reference): array
    {
        if (empty($this->publicKey) || empty($this->secretKey)) {
            throw new \InvalidArgumentException('NotchPay credentials are not configured.');
        }
        
        $response = Http::withHeaders($this->headers())
            ->get(rtrim($this->baseUrl, '/') . '/payments/' . $reference);
            
        return $this->responseToArray($response);
    }

    public function getTransaction(string $reference): array
    {
        return $this->verifyPayment($reference);
    }

    private function validatePayload(array $payload): void
    {
        $required = ['email', 'amount', 'currency', 'description', 'reference'];
        
        foreach ($required as $field) {
            if (!isset($payload[$field])) {
                throw new \InvalidArgumentException("{$field} is required");
            }
        }

        if (!is_numeric($payload['amount']) || $payload['amount'] <= 0) {
            throw new \InvalidArgumentException('Amount must be a positive number');
        }
    }

    private function responseToArray(\Illuminate\Http\Client\Response $response): array
    {
        $data = $response->json() ?? [];
        $data['statusCode'] = $response->status();
        
        if ($response->status() >= 400) {
            Log::error('NotchPay API error', [
                'status_code' => $response->status(),
                'response' => $data
            ]);
        }
        
        return $data;
    }
}