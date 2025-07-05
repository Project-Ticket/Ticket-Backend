<?php

namespace App\Services;

use App\Contracts\PaymentProviderInterface;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $paymentProvider;

    public function __construct(PaymentProviderInterface $paymentProvider)
    {
        $this->paymentProvider = $paymentProvider;
    }

    // Method to dynamically set payment provider
    public function setProvider(PaymentProviderInterface $provider)
    {
        $this->paymentProvider = $provider;
        return $this;
    }

    public function getAvailablePaymentMethods($type = null)
    {
        $query = PaymentMethod::active()->ordered();
        return $type ? $query->byType($type)->get() : $query->get();
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

    // Generic invoice creation method
    public function createInvoice($order, $paymentMethods = null)
    {
        $invoiceData = $this->prepareInvoiceData($order, $paymentMethods);
        return $this->paymentProvider->createInvoice($invoiceData);
    }

    protected function prepareInvoiceData($order, $paymentMethods = null)
    {
        $paymentMethods = $paymentMethods ?? ['QRIS'];

        return [
            "external_id" => $order->order_number,
            "amount" => $order->total_amount,
            "description" => "Payment for {$order->event->title} - Order #{$order->order_number}",
            "invoice_duration" => 86400,
            "currency" => "IDR",
            "customer" => [
                "given_names" => $order->user->name,
                "email" => $order->user->email,
            ],
            "payment_methods" => $paymentMethods,
        ];
    }

    public function getInvoiceStatus($invoiceId)
    {
        return $this->paymentProvider->getInvoiceStatus($invoiceId);
    }

    public function validatePaymentWebhook($payload)
    {
        return $this->paymentProvider->validatePaymentWebhook($payload);
    }
}
