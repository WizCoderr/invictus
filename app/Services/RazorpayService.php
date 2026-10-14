<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RazorpayService
{
    private const API = 'https://api.razorpay.com/v1';

    private string $key;

    private string $secret;

    private string $currency;

    public function __construct()
    {
        $driver = config('payment-gateway.drivers.'.config('payment-gateway.default'), []);
        $keys = $driver[config('payment-gateway.test_mode') ? 'test' : 'live'] ?? [];

        $this->key = (string) ($keys['key'] ?? '');
        $this->secret = (string) ($keys['secret'] ?? '');
        $this->currency = (string) ($driver['currency'] ?? 'INR');
    }

    /** Opens an order for $amount rupees and returns what the checkout popup needs. */
    public function createOrder(float $amount, string $receipt, array $notes = []): array
    {
        $order = $this->api()->post(self::API.'/orders', [
            'amount' => (int) round($amount * 100),
            'currency' => $this->currency,
            'receipt' => $receipt,
            'notes' => $notes,
        ])->throw()->json();

        return [
            'id' => $order['id'],
            'key' => $this->key,
            'amount' => $order['amount'],
            'currency' => $order['currency'],
        ];
    }

    /** True when the checkout's signature proves Razorpay really took this payment. */
    public function verify(string $orderId, string $paymentId, string $signature): bool
    {
        return hash_equals(hash_hmac('sha256', $orderId.'|'.$paymentId, $this->secret), $signature);
    }

    /** Every payment Razorpay holds against an order, as raw payment entities. */
    public function payments(string $orderId): array
    {
        return $this->api()->get(self::API."/orders/{$orderId}/payments")->throw()->json('items', []);
    }

    private function api(): PendingRequest
    {
        return Http::withBasicAuth($this->key, $this->secret)->acceptJson();
    }
}
