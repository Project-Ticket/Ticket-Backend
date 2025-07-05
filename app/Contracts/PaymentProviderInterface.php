<?php

namespace App\Contracts;

interface PaymentProviderInterface
{
    public function createInvoice(array $invoiceData): array;
    public function getInvoiceStatus(string $invoiceId): array;
    public function validatePaymentWebhook(array $payload): bool;
}
