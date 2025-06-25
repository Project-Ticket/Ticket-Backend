<?php

namespace App\Services;

use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $xenditSecretKey;

    public function __construct()
    {
        $this->xenditSecretKey = config('services.xendit.secret_key');
    }

    public function getAvailablePaymentMethods($type = null)
    {
        $query = PaymentMethod::active()->ordered();

        if ($type) {
            $query->byType($type);
        }

        return $query->get();
    }

    public function getPaymentMethod($code)
    {
        return PaymentMethod::where('code', $code)->active()->first();
    }

    public function calculatePaymentFee($paymentMethodCode, $amount)
    {
        $paymentMethod = $this->getPaymentMethod($paymentMethodCode);

        if (!$paymentMethod) {
            throw new \Exception("Payment method not found: {$paymentMethodCode}");
        }

        return $paymentMethod->calculateFee($amount);
    }

    public function createXenditInvoice($order, $paymentMethods = null)
    {
        try {
            // If no specific payment methods provided, use QRIS as default
            if (!$paymentMethods) {
                $paymentMethods = ['QRIS'];
            }

            $invoiceRequest = [
                "external_id" => $order->order_number,
                "amount" => $order->total_amount,
                "description" => "Payment for {$order->event->title} - Order #{$order->order_number}",
                "invoice_duration" => 86400, // 24 hours
                "currency" => "IDR",
                "customer" => [
                    "given_names" => $order->user->name,
                    "email" => $order->user->email,
                ],
                "customer_notification_preference" => [
                    "invoice_created" => ["email"],
                    "invoice_reminder" => ["email"],
                    "invoice_paid" => ["email"],
                    "invoice_expired" => ["email"]
                ],
                "payment_methods" => $paymentMethods,
                "success_redirect_url" => config('app.frontend_url') . '/orders/' . $order->uuid . '/success',
                "failure_redirect_url" => config('app.frontend_url') . '/orders/' . $order->uuid . '/failed',
            ];

            $response = Http::withBasicAuth($this->xenditSecretKey, '')
                ->post('https://api.xendit.co/v2/invoices', $invoiceRequest);

            if ($response->failed()) {
                throw new \Exception('Failed to create Xendit invoice: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Xendit invoice creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getInvoiceStatus($invoiceId)
    {
        try {
            $response = Http::withBasicAuth($this->xenditSecretKey, '')
                ->get("https://api.xendit.co/v2/invoices/{$invoiceId}");

            if ($response->failed()) {
                throw new \Exception('Failed to get invoice status: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to get invoice status: ' . $e->getMessage());
            throw $e;
        }
    }
}
