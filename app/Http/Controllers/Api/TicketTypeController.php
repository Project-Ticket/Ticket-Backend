<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TicketTypeController extends Controller
{
    protected $ticketType, $event;

    public function __construct()
    {
        $this->ticketType = new TicketType();
        $this->event = new Event();
    }

    public function index(Request $request, $eventId = null): JsonResponse
    {
        try {
            $query = $this->ticketType->with([
                'event:id,title,slug,organizer_id',
                'orders' => function ($q) {
                    $q->select('ticket_type_id', DB::raw('COUNT(*) as total_orders'))
                        ->groupBy('ticket_type_id');
                }
            ]);

            if ($eventId) {
                $query->where('event_id', $eventId);
            }

            if ($request->filled('event_id')) {
                $query->where('event_id', $request->event_id);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('description', 'like', "%{$request->search}%");
                });
            }

            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            if ($request->filled('available_only')) {
                $query->whereRaw('quantity > sold_quantity')
                    ->where('sale_start', '<=', now())
                    ->where('sale_end', '>=', now());
            }

            $sortBy = $request->get('sort_by', 'sort_order');
            $sortOrder = $request->get('sort_order', 'asc');
            $allowedSorts = ['sort_order', 'price', 'name', 'sale_start', 'sale_end', 'created_at'];

            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $ticketTypes = $query->paginate($request->get('per_page', 15))->through(function ($ticketType) {
                $ticketType->total_orders = optional($ticketType->orders->first())->total_orders ?? 0;
                $ticketType->total_revenue = $ticketType->total_orders * $ticketType->price;
                $ticketType->available_quantity = $ticketType->quantity - $ticketType->sold_quantity;

                unset($ticketType->orders);

                return $ticketType;
            });

            return MessageResponseJson::paginated('Ticket types retrieved successfully', $ticketTypes);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve ticket types', [$th->getMessage()]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'event_id'        => 'required|exists:events,id',
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'price'           => 'required|numeric|min:0|max:999999999.99',
            'quantity'        => 'required|integer|min:1',
            'min_purchase'    => 'required|integer|min:1',
            'max_purchase'    => 'required|integer|min:1|gte:min_purchase',
            'sale_start'      => 'required|date|after:now',
            'sale_end'        => 'required|date|after:sale_start',
            'is_active'       => 'boolean',
            'sort_order'      => 'integer|min:0',
            'benefits'        => 'nullable|array',
            'benefits.*'      => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
        }

        try {
            $event = $this->event->findOrFail($request->event_id);

            if (
                $request->sale_start < $event->registration_start ||
                $request->sale_end > $event->registration_end
            ) {
                return MessageResponseJson::badRequest(
                    'Ticket sale period must be within event registration period'
                );
            }

            $data = $request->except('benefits');
            $data['benefits'] = $request->benefits ? json_encode($request->benefits) : null;
            $data['sold_quantity'] = 0;
            $data['is_active'] = $request->boolean('is_active', true);
            $data['sort_order'] = $request->get('sort_order', 0);

            $ticketType = $this->ticketType->create($data);

            DB::commit();

            $ticketType->load('event:id,title,slug');

            return MessageResponseJson::created('Ticket type created successfully', $ticketType);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to create ticket type', [$th->getMessage()]);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $ticketType = $this->ticketType->with([
                'event:id,title,slug,start_datetime,registration_end',
                'orders' => function ($q) {
                    $q->select(
                        'ticket_type_id',
                        DB::raw('COUNT(*) as total_orders'),
                        DB::raw('SUM(quantity) as total_tickets_sold')
                    )
                        ->groupBy('ticket_type_id');
                },
                'orders.latestOrder' => function ($q) {
                    $q->select('id', 'ticket_type_id', 'user_id', 'created_at')
                        ->with('user:id,name,email')
                        ->latest();
                }
            ])
                ->findOrFail($id);

            $ticketType->total_orders = optional($ticketType->orders->first())->total_orders ?? 0;
            $ticketType->total_tickets_sold = optional($ticketType->orders->first())->total_tickets_sold ?? 0;
            $ticketType->total_revenue = $ticketType->total_tickets_sold * $ticketType->price;
            $ticketType->available_quantity = $ticketType->quantity - $ticketType->sold_quantity;
            $ticketType->is_sale_active = now()->between($ticketType->sale_start, $ticketType->sale_end);

            $ticketType->latest_order = optional($ticketType->orders->first()->latestOrder)->first();

            unset($ticketType->orders);

            return MessageResponseJson::success('Ticket type retrieved successfully', $ticketType);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve ticket type', [$th->getMessage()]);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'event_id'        => 'sometimes|exists:events,id',
            'name'            => 'sometimes|string|max:255',
            'description'     => 'nullable|string',
            'price'           => 'sometimes|numeric|min:0|max:999999999.99',
            'quantity'        => 'sometimes|integer|min:1',
            'min_purchase'    => 'sometimes|integer|min:1',
            'max_purchase'    => 'sometimes|integer|min:1|gte:min_purchase',
            'sale_start'      => 'sometimes|date',
            'sale_end'        => 'sometimes|date',
            'is_active'       => 'boolean',
            'sort_order'      => 'integer|min:0',
            'benefits'        => 'nullable|array',
            'benefits.*'      => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
        }

        try {
            $ticketType = $this->ticketType->findOrFail($id);

            if (!$ticketType->is_active) {
                $data = $request->except('benefits');

                if ($request->filled('benefits')) {
                    $data['benefits'] = json_encode($request->benefits);
                }

                if ($request->filled('is_active')) {
                    $data['is_active'] = $request->boolean('is_active');
                }

                $ticketType->update($data);

                DB::commit();
                $ticketType->load('event:id,title,slug');
                return MessageResponseJson::success('Ticket type updated successfully', $ticketType);
            }

            if ($ticketType->sold_quantity > 0) {
                $allowedFields = [
                    'name',
                    'description',
                    'min_purchase',
                    'max_purchase',
                    'sale_start',
                    'sale_end',
                    'sort_order',
                    'benefits',
                    'is_active'
                ];

                $restrictedFields = collect($request->keys())->diff($allowedFields);

                if ($restrictedFields->isNotEmpty()) {
                    return MessageResponseJson::badRequest(
                        'Cannot update these fields for ticket type with sold tickets: ' .
                            $restrictedFields->implode(', ')
                    );
                }

                if ($request->filled('quantity')) {
                    if ($request->quantity < $ticketType->sold_quantity) {
                        return MessageResponseJson::badRequest(
                            'Quantity cannot be less than already sold tickets'
                        );
                    }
                }

                if ($request->filled('price') && $request->price != $ticketType->price) {
                    return MessageResponseJson::badRequest(
                        'Cannot change price for ticket type with sold tickets'
                    );
                }
            }

            $data = $request->except('benefits');

            if ($request->filled('benefits')) {
                $data['benefits'] = json_encode($request->benefits);
            }

            if ($request->filled('is_active')) {
                $data['is_active'] = $request->boolean('is_active');
            }

            $ticketType->update($data);

            DB::commit();

            $ticketType->load('event:id,title,slug');

            return MessageResponseJson::success('Ticket type updated successfully', $ticketType);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to update ticket type', [$th->getMessage()]);
        }
    }

    public function destroy($id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $ticketType = $this->ticketType->findOrFail($id);

            if ($ticketType->sold_quantity > 0) {
                return MessageResponseJson::badRequest(
                    'Cannot delete ticket type as tickets have already been sold'
                );
            }

            $ticketType->delete();

            DB::commit();

            return MessageResponseJson::success('Ticket type deleted successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to delete ticket type', [$th->getMessage()]);
        }
    }

    public function toggleActive($id): JsonResponse
    {
        try {
            $ticketType = $this->ticketType->findOrFail($id);
            $ticketType->update(['is_active' => !$ticketType->is_active]);

            return MessageResponseJson::success(
                'Ticket type status updated successfully',
                $ticketType
            );
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError(
                'Failed to update ticket type status',
                [$th->getMessage()]
            );
        }
    }

    public function getAvailable($eventId): JsonResponse
    {
        try {
            $event = $this->event->findOrFail($eventId);

            $ticketTypes = $this->ticketType
                ->where('event_id', $eventId)
                ->where('is_active', true)
                ->whereRaw('quantity > sold_quantity')
                ->where('sale_start', '<=', now())
                ->where('sale_end', '>=', now())
                ->orderBy('sort_order')
                ->get()
                ->map(function ($ticketType) {
                    $ticketType->available_quantity = $ticketType->quantity - $ticketType->sold_quantity;
                    return $ticketType;
                });

            return MessageResponseJson::success(
                'Available ticket types retrieved successfully',
                [
                    'event' => $event->only(['id', 'title', 'slug']),
                    'ticket_types' => $ticketTypes
                ]
            );
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError(
                'Failed to retrieve available ticket types',
                [$th->getMessage()]
            );
        }
    }
}
