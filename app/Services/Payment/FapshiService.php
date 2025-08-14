<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FapshiService
{
    private string $baseUrl;
    private string $apiUser;
    private string $apiKey;
    private int $minAmount;

    public function __construct()
    {
        $this->baseUrl = config('services.fapshi.base_url');
        $this->apiUser = (string) config('services.fapshi.api_user');
        $this->apiKey = (string) config('services.fapshi.api_key');
        $this->minAmount = (int) config('services.fapshi.min_amount', 100);
    }

    private function headers(): array
    {
        return [
            'apiuser' => $this->apiUser,
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function initiatePay(array $payload): array
    {
        $this->validateAmount($payload['amount'] ?? null);
        $response = Http::withHeaders($this->headers())
            ->post(rtrim($this->baseUrl, '/') . '/initiate-pay', $payload);
        return $this->responseToArray($response);
    }

    public function directPay(array $payload): array
    {
        $this->validateAmount($payload['amount'] ?? null);
        if (empty($payload['phone'])) {
            throw new \InvalidArgumentException('phone required');
        }
        $response = Http::withHeaders($this->headers())
            ->post(rtrim($this->baseUrl, '/') . '/direct-pay', $payload);
        return $this->responseToArray($response);
    }

    public function paymentStatus(string $transId): array
    {
        $response = Http::withHeaders($this->headers())
            ->get(rtrim($this->baseUrl, '/') . '/payment-status/' . $transId);
        return $this->responseToArray($response);
    }

    public function expirePay(string $transId): array
    {
        $response = Http::withHeaders($this->headers())
            ->post(rtrim($this->baseUrl, '/') . '/expire-pay', ['transId' => $transId]);
        return $this->responseToArray($response);
    }

    public function search(array $params): array
    {
        $response = Http::withHeaders($this->headers())
            ->get(rtrim($this->baseUrl, '/') . '/search', $params);
        return $this->responseToArray($response);
    }

    public function balance(): array
    {
        $response = Http::withHeaders($this->headers())
            ->get(rtrim($this->baseUrl, '/') . '/balance');
        return $this->responseToArray($response);
    }

    public function payout(array $payload): array
    {
        $this->validateAmount($payload['amount'] ?? null);
        if (empty($payload['phone'])) {
            throw new \InvalidArgumentException('phone required');
        }
        $response = Http::withHeaders($this->headers())
            ->post(rtrim($this->baseUrl, '/') . '/payout', $payload);
        return $this->responseToArray($response);
    }

    private function validateAmount($amount): void
    {
        if (!is_int($amount)) {
            throw new \InvalidArgumentException('amount must be integer in XAF');
        }
        if ($amount < $this->minAmount) {
            throw new \InvalidArgumentException('amount must be >= ' . $this->minAmount);
        }
    }

    private function responseToArray(\Illuminate\Http\Client\Response $response): array
    {
        $data = $response->json() ?? [];
        $data['statusCode'] = $response->status();
        return $data;
    }
}



