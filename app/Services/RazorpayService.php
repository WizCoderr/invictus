<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RazorpayService
{
    private const API = 'https://api.razorpay.com/v1';

    /** Opens an order for $amount rupees and returns what the checkout popup needs. */
    public function createOrder(float $amount, string $receipt, array $notes = []): array
    {
        $order = $this->api()->post(self::API.'/orders', [
            'amount' => (int) round($amount * 100),
            'currency' => config('services.razorpay.currency'),
            'receipt' => $receipt,
            'notes' => $notes,
        ])->throw()->json();

        return [
            'id' => $order['id'],
            'key' => config('services.razorpay.key'),
            'amount' => $order['amount'],
            'currency' => $order['currency'],
        ];
    }

    /** True when the checkout's signature proves Razorpay really took this payment. */
    public function verify(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, (string) config('services.razorpay.secret'));

        return hash_equals($expected, $signature);
    }

    /** Every payment Razorpay holds against an order, as raw payment entities. */
    public function payments(string $orderId): array
    {
        return $this->api()->get(self::API."/orders/{$orderId}/payments")->throw()->json('items', []);
    }

    private function api(): PendingRequest
    {
        return Http::withBasicAuth((string) config('services.razorpay.key'), (string) config('services.razorpay.secret'))
            ->acceptJson();
    }
}
