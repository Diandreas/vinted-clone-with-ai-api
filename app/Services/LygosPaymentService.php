<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class LygosPaymentService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.lygos.base_url', 'https://api.lygosapp.com/v1');
        $this->apiKey = config('services.lygos.api_key');
    }

    /**
     * Create a payment gateway for product publishing fees
     */
    public function createPaymentGateway(array $data): array
    {
        if (!$this->apiKey) {
            throw new Exception('Lygos API key is not configured');
        }
        
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/gateway', $data);

            if (!$response->successful()) {
                Log::error('Lygos payment gateway creation failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'data' => $data
                ]);
                
                throw new Exception('Failed to create payment gateway: ' . $response->json('detail.message', 'Unknown error'));
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Lygos payment gateway error', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            
            throw $e;
        }
    }

    /**
     * Get payment gateway by ID
     */
    public function getPaymentGateway(string $gatewayId): array
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey
            ])->get($this->baseUrl . '/gateway/' . $gatewayId);

            if (!$response->successful()) {
                throw new Exception('Failed to get payment gateway');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Failed to get Lygos payment gateway', [
                'gateway_id' => $gatewayId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * List all payment gateways
     */
    public function listPaymentGateways(): array
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey
            ])->get($this->baseUrl . '/gateway');

            if (!$response->successful()) {
                throw new Exception('Failed to list payment gateways');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Failed to list Lygos payment gateways', [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Update payment gateway
     */
    public function updatePaymentGateway(string $gatewayId, array $data): array
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->put($this->baseUrl . '/gateway/' . $gatewayId, $data);

            if (!$response->successful()) {
                throw new Exception('Failed to update payment gateway');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Failed to update Lygos payment gateway', [
                'gateway_id' => $gatewayId,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Delete payment gateway
     */
    public function deletePaymentGateway(string $gatewayId): bool
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey
            ])->delete($this->baseUrl . '/gateway/' . $gatewayId);

            if (!$response->successful()) {
                throw new Exception('Failed to delete payment gateway');
            }

            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete Lygos payment gateway', [
                'gateway_id' => $gatewayId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}