<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\EventOrganizer;
use App\Models\Order;
use App\Models\TicketType;
use App\Services\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class WebhookController extends Controller
{
    public function webhook(Request $request): JsonResponse
    {
        $webhookToken = config('services.xendit.webhook_token');
        $signature = $request->header('x-callback-token');

        if ($signature !== $webhookToken) {
            return MessageResponseJson::forbidden('Invalid webhook signature');
        }

        $payload = $request->all();

        try {
            DB::beginTransaction();

            if (isset($payload['external_id'])) {
                $externalId = $payload['external_id'];
                if (Str::startsWith($externalId, 'ORD')) {
                    $order = Order::where('payment_reference', $payload['id'])->first();

                    if (!$order) {
                        return MessageResponseJson::notFound('Order not found for this payment reference');
                    }

                    if ($payload['status'] === 'PAID') {
                        $order->update([
                            'payment_status' => 'paid',
                            'paid_at' => now(),
                            'status' => Status::getId('orderStatus', 'PAID'),
                        ]);

                        $order->tickets()->update(['status' => Status::getId('ticketStatus', 'ACTIVE')]);
                    } elseif (in_array($payload['status'], ['EXPIRED', 'FAILED'])) {
                        $order->update([
                            'payment_status' => 'failed',
                            'status' => Status::getId('orderStatus', 'CANCELLED'),
                        ]);

                        foreach ($order->orderItems as $item) {
                            TicketType::find($item->ticket_type_id)
                                ->decrement('sold_quantity', $item->quantity);
                        }

                        $order->tickets()->update(['status' => Status::getId('ticketStatus', 'INACTIVE')]);
                    }
                } elseif (Str::startsWith($externalId, 'EO-Application-')) {
                    $eventOrganizer = EventOrganizer::where('payment_reference', $payload['id'])->first();

                    if (!$eventOrganizer) {
                        return MessageResponseJson::notFound('Event Organizer not found for this payment reference');
                    }

                    if ($payload['status'] === 'PAID') {
                        $eventOrganizer->update([
                            'payment_status' => 'paid',
                            'paid_at' => now(),
                            'application_status' => 'under_review',
                        ]);
                    } elseif (in_array($payload['status'], ['EXPIRED', 'FAILED'])) {
                        $eventOrganizer->update([
                            'payment_status' => 'failed',
                            'application_status' => 'pending',
                        ]);

                        if ($eventOrganizer->logo) {
                            Storage::disk('public')->delete($eventOrganizer->logo);
                        }
                        if ($eventOrganizer->banner) {
                            Storage::disk('public')->delete($eventOrganizer->banner);
                        }
                        if ($eventOrganizer->uploaded_documents) {
                            $uploadedDocuments = json_decode($eventOrganizer->uploaded_documents, true);
                            foreach ($uploadedDocuments as $doc) {
                                Storage::disk('public')->delete($doc);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return MessageResponseJson::success('Webhook processed successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to process webhook', [$th->getMessage()]);
        }
    }
}
