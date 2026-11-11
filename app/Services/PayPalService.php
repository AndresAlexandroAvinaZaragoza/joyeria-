<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayPalService
{
    public function createOrder(string $amount, string $currency): array
    {
        $response = $this->client()
            ->post('/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => $amount,
                    ],
                ]],
            ])
            ->throw();

        return $response->json();
    }

    public function captureOrder(string $orderId): array
    {
        return $this->client()
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->send('POST', "/v2/checkout/orders/{$orderId}/capture", [
                'body' => '',
            ])
            ->throw()
            ->json();
    }

    public function getOrder(string $orderId): array
    {
        return $this->client()
            ->get("/v2/checkout/orders/{$orderId}")
            ->throw()
            ->json();
    }

    private function client(): PendingRequest
    {
        $clientId = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.client_secret');

        if (! $clientId || ! $clientSecret) {
            throw new RuntimeException('PayPal no está configurado. Define PAYPAL_CLIENT_ID y PAYPAL_CLIENT_SECRET.');
        }

        $baseUrl = rtrim(config('services.paypal.base_url'), '/');
        $token = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials'])
            ->throw()
            ->json('access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('PayPal no devolvió un token de acceso válido.');
        }

        return Http::baseUrl($baseUrl)
            ->withToken($token)
            ->acceptJson()
            ->timeout(15);
    }
}
