<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditPaymentProvider implements PaymentProviderInterface
{
    protected $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.xendit.secret_key');
    }

    public function createInvoice(array $invoiceData): array
    {
        $defaultConfig = [
            'currency' => 'IDR',
            'invoice_duration' => 86400,
            'customer_notification_preference' => [
                'invoice_created' => ['whatsapp', 'email', 'viber'],
                'invoice_reminder' => ['whatsapp', 'email', 'viber'],
                'invoice_paid' => ['whatsapp', 'email', 'viber']
            ],
            'success_redirect_url' => 'https://www.google.com',
            'failure_redirect_url' => 'https://www.google.com',
            "metadata" => [
                "store_branch" => "Jakarta"
            ]
        ];

        $mergedInvoiceData = array_merge($defaultConfig, $invoiceData);

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post('https://api.xendit.co/v2/invoices', $mergedInvoiceData);

            if ($response->failed()) {
                throw new \Exception('Invoice creation failed: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Xendit invoice creation error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getInvoiceStatus(string $invoiceId): array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("https://api.xendit.co/v2/invoices/{$invoiceId}");

            if ($response->failed()) {
                throw new \Exception('Get invoice status failed: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Get invoice status error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function validatePaymentWebhook(array $payload): bool
    {
        return true;
    }
}
