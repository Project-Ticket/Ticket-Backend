<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Services\PaymentService;
use App\Services\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Contracts\PaymentProviderInterface;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    protected $order, $orderItem, $event, $ticketType, $ticket, $paymentService, $paymentProvider;

    public function __construct(
        PaymentService $paymentService,
        PaymentProviderInterface $paymentProvider
    ) {
        $this->order = new Order();
        $this->orderItem = new OrderItem();
        $this->event = new Event();
        $this->ticketType = new TicketType();
        $this->ticket = new Ticket();
        $this->paymentService = $paymentService;
        $this->paymentProvider = $paymentProvider;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->order->with([
                'user:id,name,email',
                'event:id,title,slug,start_datetime,end_datetime',
                'orderItems:id,order_id,ticket_type_id,quantity,unit_price,total_price',
                'orderItems.ticketType:id,name,price'
            ]);

            if (!Auth::user()->hasRole('Admin')) {
                $query->where('user_id', Auth::id());
            }

            foreach (['user_id', 'event_id', 'status', 'payment_status'] as $filter) {
                if ($request->filled($filter)) {
                    $query->where($filter, $request->$filter);
                }
            }

            if ($request->filled('order_number')) {
                $query->where('order_number', 'like', "%{$request->order_number}%");
            }

            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $allowedSorts = ['created_at', 'order_number', 'total_amount', 'paid_at', 'expired_at'];

            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $orders = $query->paginate($request->get('per_page', 15));

            return MessageResponseJson::paginated('Orders retrieved successfully', $orders);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve orders', [$th->getMessage()]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'items' => 'required|array|min:1',
            'items.*.ticket_type_id' => 'required|exists:ticket_types,id',
            'items.*.quantity' => 'required|integer|min:1|max:10',
            'items.*.attendees' => 'required|array',
            'items.*.attendees.*.name' => 'required|string|max:255',
            'items.*.attendees.*.email' => 'required|email|max:255',
            'items.*.attendees.*.phone' => 'nullable|string|max:20',
            'payment_method' => 'required|string|exists:payment_methods,code',
            'admin_fee' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'success_redirect_url' => 'required|url',
            'failure_redirect_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
        }

        try {
            $event = $this->event->findOrFail($request->event_id);
            $user = Auth::user();

            if ($event->status !== $this->event::STATUS_PUBLISHED) {
                return MessageResponseJson::badRequest('Event is not available for booking');
            }

            $paymentMethod = $this->paymentService->getPaymentMethod($request->payment_method);
            if (!$paymentMethod) {
                return MessageResponseJson::badRequest('Payment method is not available');
            }

            $subtotal = 0;
            $orderItems = [];
            $tickets = [];

            foreach ($request->items as $item) {
                $ticketType = $this->ticketType->findOrFail($item['ticket_type_id']);

                if ($ticketType->event_id !== $request->event_id) {
                    return MessageResponseJson::badRequest("Ticket type {$ticketType->name} does not belong to this event");
                }

                $availableQuantity = $ticketType->quantity - $ticketType->sold_quantity;
                if ($item['quantity'] > $availableQuantity) {
                    return MessageResponseJson::badRequest("Not enough tickets available for {$ticketType->name}. Available: {$availableQuantity}");
                }

                if (count($item['attendees']) !== $item['quantity']) {
                    return MessageResponseJson::badRequest('Number of attendees must match ticket quantity');
                }

                $unitPrice = $ticketType->price;
                $totalPrice = $unitPrice * $item['quantity'];
                $subtotal += $totalPrice;

                $orderItems[] = [
                    'ticket_type_id' => $item['ticket_type_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'attendees' => $item['attendees'],
                ];
            }

            $adminFee = $request->admin_fee ?? 5000;
            $paymentFee = $this->paymentService->calculatePaymentFee($request->payment_method, $subtotal);
            $discountAmount = $request->discount_amount ?? 0;
            $totalAmount = $subtotal + $adminFee + $paymentFee - $discountAmount;

            if ($totalAmount < 0) {
                return MessageResponseJson::badRequest('Total amount cannot be negative');
            }

            $orderNumber = Helper::generateUniqueOrderNumber();
            if ($this->order->where('order_number', $orderNumber)->exists()) {
                $orderNumber = Helper::generateUniqueOrderNumber();
            }

            $order = $this->order->create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'event_id' => $request->event_id,
                'subtotal' => $subtotal,
                'admin_fee' => $adminFee,
                'payment_fee' => $paymentFee,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'status' => $this->order::STATUS_PENDING, // Status 1
                'payment_status' => 'unpaid',
                'payment_method' => $request->payment_method, // Store the code
                'expired_at' => now()->addHours(24),
                'notes' => $request->notes,
            ]);

            foreach ($orderItems as $orderItem) {
                $createdOrderItem = $this->orderItem->create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $orderItem['ticket_type_id'],
                    'quantity' => $orderItem['quantity'],
                    'unit_price' => $orderItem['unit_price'],
                    'total_price' => $orderItem['total_price'],
                ]);

                $ticketType = $this->ticketType->find($orderItem['ticket_type_id']);
                $ticketType->increment('sold_quantity', $orderItem['quantity']);

                foreach ($orderItem['attendees'] as $attendee) {

                    $ticketCode = Helper::generateQrCode();
                    if ($this->ticket->where('ticket_code', $ticketCode)->exists()) {
                        $ticketCode = Helper::generateQrCode();
                    }

                    $qrCode = Helper::generateQrCode();
                    if ($this->ticket->where('qr_code', $qrCode)->exists()) {
                        $qrCode = Helper::generateQrCode();
                    }

                    $ticket = $this->ticket->create([
                        'ticket_code' => $ticketCode,
                        'qr_code' => $qrCode,
                        'order_id' => $order->id,
                        'ticket_type_id' => $orderItem['ticket_type_id'],
                        'user_id' => $user->id,
                        'attendee_name' => $attendee['name'],
                        'attendee_email' => $attendee['email'],
                        'attendee_phone' => $attendee['phone'] ?? null,
                        'status' => $this->ticket::STATUS_PENDING_PAYMENT,
                    ]);

                    $tickets[] = $ticket;
                }
            }

            try {
                $order->load(['user', 'event']);

                $invoiceData = [
                    'external_id' => $order->order_number,
                    'amount' => $order->total_amount,
                    'description' => "Payment for {$order->event->title} - Order #{$order->order_number}",
                    'invoice_duration' => 86400,
                    'currency' => 'IDR',
                    'customer' => [
                        'given_names' => $order->user->name,
                        'email' => $order->user->email,
                    ],
                    'payment_methods' => [$request->payment_method],
                    "success_redirect_url" => $request->success_redirect_url,
                    "failure_redirect_url" => $request->failure_redirect_url,
                ];

                $invoiceResult = $this->paymentProvider->createInvoice($invoiceData);

                $order->update([
                    'payment_reference' => $invoiceResult['id'],
                ]);

                DB::commit();

                $order->load([
                    'user:id,name,email',
                    'event:id,title,slug,start_datetime,end_datetime,venue_name,venue_address',
                    'orderItems:id,order_id,ticket_type_id,quantity,unit_price,total_price',
                    'orderItems.ticketType:id,name,price,benefits',
                    'tickets:id,ticket_code,qr_code,attendee_name,attendee_email,status'
                ]);

                return MessageResponseJson::created('Order created successfully', [
                    'order' => $order,
                    'payment_url' => $invoiceResult['invoice_url'],
                    'invoice_id' => $invoiceResult['id'],
                    'tickets_count' => count($tickets),
                    'payment_method' => $paymentMethod->name,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return MessageResponseJson::serverError('Failed to create payment invoice', [$e->getMessage()]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to create order', [$th->getMessage()]);
        }
    }

    public function show($uuid): JsonResponse
    {
        try {
            $query = $this->order->with([
                'user:id,name,email',
                'event:id,title,slug,start_datetime,end_datetime,venue_name,venue_address',
                'orderItems:id,order_id,ticket_type_id,quantity,unit_price,total_price',
                'orderItems.ticketType:id,name,price,benefits',
                'tickets:id,ticket_code,qr_code,attendee_name,attendee_email,attendee_phone,status'
            ]);

            if (!Auth::user()->hasRole('Admin')) {
                $query->where('user_id', Auth::id());
            }

            $order = $query->where('uuid', $uuid)->first();

            if (!$order) {
                return MessageResponseJson::notFound('Order not found');
            }

            return MessageResponseJson::success('Order retrieved successfully', $order);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve order', [$th->getMessage()]);
        }
    }

    public function update(Request $request, $uuid): JsonResponse
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'payment_method' => 'nullable|string|exists:payment_methods,code',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
        }

        try {
            $query = $this->order->query();

            if (!Auth::user()->hasRole('Admin')) {
                $query->where('user_id', Auth::id());
            }

            $order = $query->where('uuid', $uuid)->first();

            if (!$order) {
                return MessageResponseJson::notFound('Order not found');
            }

            if ($order->status !== $this->order::STATUS_PENDING) {
                return MessageResponseJson::badRequest("Cannot update order with status Pending`");
            }

            if ($order->payment_status === 'paid' || $order->expired_at < now()) {
                return MessageResponseJson::badRequest('Order cannot be modified');
            }

            $forbiddenFields = ['payment_status', 'status', 'user_id', 'event_id', 'subtotal', 'total_amount'];
            foreach ($forbiddenFields as $field) {
                if ($request->has($field)) {
                    return MessageResponseJson::badRequest("Cannot update field: {$field}");
                }
            }

            $updateData = [];

            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }

            if ($request->has('payment_method')) {
                $newPaymentMethodCode = $request->payment_method;

                $paymentMethod = $this->paymentService->getPaymentMethod($newPaymentMethodCode);
                if (!$paymentMethod) {
                    return MessageResponseJson::badRequest('Payment method is not available');
                }

                if ($order->payment_method !== $newPaymentMethodCode) {
                    $newPaymentFee = $this->paymentService->calculatePaymentFee(
                        $newPaymentMethodCode,
                        $order->subtotal
                    );

                    $newTotalAmount = $order->subtotal + $order->admin_fee + $newPaymentFee - $order->discount_amount;

                    if ($newTotalAmount < 0) {
                        return MessageResponseJson::badRequest('Total amount cannot be negative');
                    }

                    $updateData['payment_method'] = $newPaymentMethodCode;
                    $updateData['payment_fee'] = $newPaymentFee;
                    $updateData['total_amount'] = $newTotalAmount;

                    try {
                        $order->load(['user', 'event']);
                        $invoiceResult = $this->paymentProvider->createInvoice([
                            'external_id' => $order->order_number,
                            'amount' => $newTotalAmount,
                            'description' => "Payment for {$order->event->title} - Order #{$order->order_number}",
                            'payment_methods' => [$newPaymentMethodCode],
                            "success_redirect_url" => $request->success_redirect_url,
                            "failure_redirect_url" => $request->failure_redirect_url,
                        ]);

                        $updateData['payment_reference'] = $invoiceResult['id'];
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return MessageResponseJson::serverError('Failed to create new payment invoice', [$e->getMessage()]);
                    }
                }
            }

            if (empty($updateData)) {
                return MessageResponseJson::badRequest('No valid fields to update');
            }

            $order->update($updateData);

            DB::commit();

            $order->load([
                'user:id,name,email',
                'event:id,title,slug,start_datetime,end_datetime',
                'orderItems:id,order_id,ticket_type_id,quantity,unit_price,total_price',
                'orderItems.ticketType:id,name,price'
            ]);

            $responseData = ['order' => $order];

            if (isset($invoiceResult)) {
                $responseData['payment_url'] = $invoiceResult['invoice_url'] ?? null;
                $responseData['invoice_id'] = $invoiceResult['id'] ?? null;
                $responseData['payment_method'] = $paymentMethod->name ?? null;
            }

            return MessageResponseJson::success('Order updated successfully', $responseData);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to update order', [$th->getMessage()]);
        }
    }

    public function cancel($uuid): JsonResponse
    {
        DB::beginTransaction();

        try {
            $query = $this->order->query();

            if (!Auth::user()->hasRole('Admin')) {
                $query->where('user_id', Auth::id());
            }

            $order = $query->where('uuid', $uuid)->first();

            if (!$order) {
                return MessageResponseJson::notFound('Order not found');
            }

            if ($order->payment_status === 'paid') {
                return MessageResponseJson::badRequest('Cannot cancel paid order');
            }


            if ($order->status === $this->order::STATUS_CANCELED) {
                return MessageResponseJson::badRequest('Order is already cancelled');
            }

            $order->update([
                'status' => $this->order::STATUS_CANCELED,
                'payment_status' => 'failed'
            ]);

            foreach ($order->orderItems as $item) {
                $this->ticketType->find($item->ticket_type_id)->decrement('sold_quantity', $item->quantity);
            }

            DB::commit();

            return MessageResponseJson::success('Order cancelled successfully', $order);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to cancel order', [$th->getMessage()]);
        }
    }

    public function myOrders(Request $request): JsonResponse
    {
        try {
            $query = $this->order->with([
                'event:id,title,slug,start_datetime,end_datetime,banner_image',
                'orderItems:id,order_id,ticket_type_id,quantity,unit_price,total_price',
                'orderItems.ticketType:id,name,price',
                'tickets:id,ticket_code,attendee_name,status'
            ])->where('user_id', Auth::id());

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            $orders = $query->orderByDesc('created_at')
                ->paginate($request->get('per_page', 15));

            return MessageResponseJson::paginated('Your orders retrieved successfully', $orders);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve your orders', [$th->getMessage()]);
        }
    }

    public function statistics(Request $request): JsonResponse
    {
        try {
            if (!Auth::user()->hasRole('User')) {
                return MessageResponseJson::forbidden('Unauthorized access');
            }

            $dateFrom = $request->get('date_from', now()->startOfMonth());
            $dateTo = $request->get('date_to', now()->endOfMonth());

            $stats = [
                'total_orders' => $this->order->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'paid_orders' => $this->order->where('payment_status', 'paid')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'unpaid_orders' => $this->order->where('payment_status', 'unpaid')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'cancelled_orders' => $this->order->where('status', 0)
                    ->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_revenue' => $this->order->where('payment_status', 'paid')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('total_amount'),
                'average_order_value' => $this->order->where('payment_status', 'paid')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->avg('total_amount'),
            ];

            return MessageResponseJson::success('Order statistics retrieved successfully', $stats);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve statistics', [$th->getMessage()]);
        }
    }
}
