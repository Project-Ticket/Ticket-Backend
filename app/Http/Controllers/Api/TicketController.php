<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Rules\ValidateStatus;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketController extends Controller
{
    protected $ticket, $ticketType, $order;

    public function __construct()
    {
        $this->ticket = new Ticket();
        $this->ticketType = new TicketType();
        $this->order = new Order();
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->ticket->with([
                'ticketType:id,event_id,name,price',
                'ticketType.event:id,title,slug,start_datetime,end_datetime',
                'user:id,name,email',
                'order:id,order_number,status',
                'usedBy:id,name'
            ]);

            if (!Auth::user()->hasRole('Admin')) {
                $query->where('user_id', Auth::id());
            }

            foreach (['user_id', 'ticket_type_id', 'order_id', 'status'] as $filter) {
                if ($request->filled($filter)) {
                    $query->where($filter, $request->$filter);
                }
            }

            if ($request->filled('event_id')) {
                $query->whereHas('ticketType', function ($q) use ($request) {
                    $q->where('event_id', $request->event_id);
                });
            }

            if ($request->filled('ticket_code')) {
                $query->where('ticket_code', 'like', "%{$request->ticket_code}%");
            }

            if ($request->filled('attendee_name')) {
                $query->where('attendee_name', 'like', "%{$request->attendee_name}%");
            }

            if ($request->filled('attendee_email')) {
                $query->where('attendee_email', 'like', "%{$request->attendee_email}%");
            }

            if ($request->filled('used_status')) {
                if ($request->used_status === 'used') {
                    $query->whereNotNull('used_at');
                } else {
                    $query->whereNull('used_at');
                }
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $allowedSorts = ['created_at', 'ticket_code', 'attendee_name', 'used_at', 'status'];

            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $tickets = $query->paginate($request->get('per_page', 15));

            return MessageResponseJson::paginated('Tickets retrieved successfully', $tickets);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve tickets', [$th->getMessage()]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'order_id'          => 'required|exists:orders,id',
            'ticket_type_id'    => 'required|exists:ticket_types,id',
            'user_id'           => 'required|exists:users,id',
            'quantity'          => 'required|integer|min:1|max:10',
            'attendees'         => 'required|array|size:' . $request->quantity,
            'attendees.*.name'  => 'required|string|max:255',
            'attendees.*.email' => 'required|email|max:255',
            'attendees.*.phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
        }

        try {
            $order = $this->order->findOrFail($request->order_id);
            $ticketType = $this->ticketType->findOrFail($request->ticket_type_id);

            if (!Auth::user()->hasRole('Admin') && $order->user_id !== Auth::id()) {
                return MessageResponseJson::forbidden('Unauthorized to create tickets for this order');
            }

            $availableQuantity = $ticketType->quantity - $ticketType->sold_quantity;
            if ($request->quantity > $availableQuantity) {
                return MessageResponseJson::badRequest('Not enough tickets available');
            }

            $tickets = [];
            foreach ($request->attendees as $attendee) {
                $ticketCode = $this->generateUniqueTicketCode();
                $qrCode = $this->generateUniqueQrCode();

                $ticket = $this->ticket->create([
                    'ticket_code' => $ticketCode,
                    'qr_code' => $qrCode,
                    'order_id' => $request->order_id,
                    'ticket_type_id' => $request->ticket_type_id,
                    'user_id' => $request->user_id,
                    'attendee_name' => $attendee['name'],
                    'attendee_email' => $attendee['email'],
                    'attendee_phone' => $attendee['phone'] ?? null,
                    'status' => 1,
                ]);

                $tickets[] = $ticket;
            }

            $ticketType->increment('sold_quantity', $request->quantity);

            DB::commit();

            $tickets = collect($tickets)->map(function ($ticket) {
                return $ticket->load([
                    'ticketType:id,name,price',
                    'ticketType.event:id,title,slug',
                    'user:id,name,email'
                ]);
            });

            return MessageResponseJson::created('Tickets created successfully', $tickets);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to create tickets', [$th->getMessage()]);
        }
    }

    /**
     * Show a specific ticket
     */
    public function show($uuid): JsonResponse
    {
        try {
            $query = $this->ticket->with([
                'ticketType:id,event_id,name,price,benefits',
                'ticketType.event:id,title,slug,start_datetime,end_datetime,venue_name,venue_address,online_platform,online_link',
                'user:id,name,email',
                'order:id,order_number,status,total_amount',
                'usedBy:id,name'
            ]);

            if (!Auth::user()->hasRole('Admin')) {
                $query->where('user_id', Auth::id());
            }

            $ticket = $query->where('uuid', $uuid)->first();

            if (!$ticket) {
                return MessageResponseJson::notFound('Ticket not found');
            }

            return MessageResponseJson::success('Ticket retrieved successfully', $ticket);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve ticket', [$th->getMessage()]);
        }
    }

    /**
     * Update ticket information
     */
    public function update(Request $request, $uuid): JsonResponse
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'attendee_name'  => 'sometimes|string|max:255',
            'attendee_email' => 'sometimes|email|max:255',
            'attendee_phone' => 'nullable|string|max:20',
            'notes'          => 'nullable|string',
            'status'         => ['sometimes', new ValidateStatus('ticketStatus')],
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
        }

        try {
            $query = $this->ticket->query();

            // If not admin, only allow updating own tickets
            if (!Auth::user()->hasRole('Admin')) {
                $query->where('user_id', Auth::id());
            }

            $ticket = $query->where('uuid', $uuid)->first();

            if (!$ticket) {
                return MessageResponseJson::notFound('Ticket not found');
            }

            // Don't allow updates if ticket is already used
            if ($ticket->used_at) {
                return MessageResponseJson::badRequest('Cannot update used ticket');
            }

            $ticket->update($request->only([
                'attendee_name',
                'attendee_email',
                'attendee_phone',
                'notes',
                'status'
            ]));

            DB::commit();

            $ticket->load([
                'ticketType:id,name,price',
                'ticketType.event:id,title,slug',
                'user:id,name,email'
            ]);

            return MessageResponseJson::success('Ticket updated successfully', $ticket);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to update ticket', [$th->getMessage()]);
        }
    }

    /**
     * Use/scan a ticket
     */
    // public function useTicket(Request $request): JsonResponse
    // {
    //     DB::beginTransaction();

    //     $validator = Validator::make($request->all(), [
    //         'ticket_code' => 'required_without:qr_code|string',
    //         'qr_code'     => 'required_without:ticket_code|string',
    //         'notes'       => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
    //     }

    //     try {
    //         $query = $this->ticket->with([
    //             'ticketType:id,event_id,name',
    //             'ticketType.event:id,title,start_datetime,end_datetime',
    //             'user:id,name,email'
    //         ]);

    //         if ($request->filled('ticket_code')) {
    //             $query->where('ticket_code', $request->ticket_code);
    //         } else {
    //             $query->where('qr_code', $request->qr_code);
    //         }

    //         $ticket = $query->first();

    //         if (!$ticket) {
    //             return MessageResponseJson::notFound('Ticket not found');
    //         }

    //         if ($ticket->used_at) {
    //             return MessageResponseJson::badRequest(
    //                 'Ticket has already been used on ' . $ticket->used_at->format('Y-m-d H:i:s')
    //             );
    //         }

    //         if ($ticket->status !== 1) {
    //             return MessageResponseJson::badRequest('Ticket is not active');
    //         }

    //         // Check if event is currently happening
    //         $now = now();
    //         $event = $ticket->ticketType->event;

    //         if ($now->lt($event->start_datetime)) {
    //             return MessageResponseJson::badRequest('Event has not started yet');
    //         }

    //         if ($now->gt($event->end_datetime)) {
    //             return MessageResponseJson::badRequest('Event has already ended');
    //         }

    //         $ticket->update([
    //             'used_at' => now(),
    //             'used_by' => Auth::id(),
    //             'notes' => $request->notes
    //         ]);

    //         DB::commit();

    //         return MessageResponseJson::success('Ticket used successfully', $ticket);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return MessageResponseJson::serverError('Failed to use ticket', [$th->getMessage()]);
    //     }
    // }

    /**
     * Get tickets by QR code (for validation)
     */
    public function getByQrCode($qrCode): JsonResponse
    {
        try {
            $ticket = $this->ticket->with([
                'ticketType:id,event_id,name,price',
                'ticketType.event:id,title,slug,start_datetime,end_datetime',
                'user:id,name,email',
                'usedBy:id,name'
            ])->where('qr_code', $qrCode)->first();

            if (!$ticket) {
                return MessageResponseJson::notFound('Ticket not found');
            }

            // Add validation info
            $ticket->is_valid = $ticket->status === 1 && !$ticket->used_at;
            $ticket->validation_message = $this->getValidationMessage($ticket);

            return MessageResponseJson::success('Ticket retrieved successfully', $ticket);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve ticket', [$th->getMessage()]);
        }
    }

    /**
     * Get user's tickets
     */
    public function myTickets(Request $request): JsonResponse
    {
        try {
            $query = $this->ticket->with([
                'ticketType:id,event_id,name,price',
                'ticketType.event:id,title,slug,start_datetime,end_datetime,banner_image',
                'order:id,order_number,status'
            ])->where('user_id', Auth::id());

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('upcoming')) {
                $query->whereHas('ticketType.event', function ($q) {
                    $q->where('start_datetime', '>', now());
                });
            }

            $tickets = $query->orderByDesc('created_at')
                ->paginate($request->get('per_page', 15));

            return MessageResponseJson::paginated('Your tickets retrieved successfully', $tickets);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve your tickets', [$th->getMessage()]);
        }
    }

    /**
     * Generate QR code image for ticket
     */
    public function generateQrCodeImage($uuid)
    {
        try {
            $ticket = $this->ticket->where('uuid', $uuid)
                ->where('user_id', Auth::id())
                ->first();

            if (!$ticket) {
                return MessageResponseJson::notFound('Ticket not found');
            }

            if ($ticket->used_at !== null || $ticket->used_by !== null || $ticket->status !== 2) {
                return MessageResponseJson::badRequest('Ticket cannot generate QR code. Ensure it is unused and has status set to 2.');
            }

            $qrCode = QrCode::format('png')
                ->size(300)
                ->margin(2)
                ->generate(Crypt::encrypt($ticket->qr_code));

            return response($qrCode)
                ->header('Content-Type', 'image/png');
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to generate QR code', [$th->getMessage()]);
        }
    }

    public function getTicketFromQrCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
        }

        try {
            $decryptedQrCode = Crypt::decrypt($request->qr_code);

            $ticket = $this->ticket->with(['ticketType'])
                ->where('qr_code', $decryptedQrCode)
                ->first();

            if (!$ticket) {
                return MessageResponseJson::notFound('Ticket not found');
            }

            return MessageResponseJson::success('Ticket retrieved successfully', $ticket);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve ticket', [$th->getMessage()]);
        }
    }

    public function markTicketAsUsed(Request $request)
    {
        DB::beginTransaction();

        try {

            $ticket = $this->ticket->with(['ticketType'])
                ->where('qr_code', $request->qr_code)
                ->first();

            if ($ticket->used_at !== null) {
                return MessageResponseJson::badRequest('Ticket already used on ' . $ticket->used_at->format('Y-m-d H:i:s'));
            }

            $ticket->update([
                'used_at' => now(),
                'used_by' => Auth::id() // Simpan ID pengguna yang menggunakan tiket
            ]);

            DB::commit();

            return MessageResponseJson::success('Ticket marked as used successfully', $ticket);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to mark ticket as used', [$th->getMessage()]);
        }
    }

    /**
     * Generate unique ticket code
     */
    private function generateUniqueTicketCode(): string
    {
        do {
            $code = 'TKT-' . strtoupper(Str::random(8));
        } while ($this->ticket->where('ticket_code', $code)->exists());

        return $code;
    }

    /**
     * Generate unique QR code
     */
    private function generateUniqueQrCode(): string
    {
        do {
            $code = strtoupper(Str::random(16));
        } while ($this->ticket->where('qr_code', $code)->exists());

        return $code;
    }

    /**
     * Get validation message for ticket
     */
    private function getValidationMessage($ticket): string
    {
        if ($ticket->used_at) {
            return 'Ticket already used on ' . $ticket->used_at->format('Y-m-d H:i:s');
        }

        if ($ticket->status !== 1) {
            return 'Ticket is not active';
        }

        $now = now();
        $event = $ticket->ticketType->event;

        if ($now->lt($event->start_datetime)) {
            return 'Event has not started yet';
        }

        if ($now->gt($event->end_datetime)) {
            return 'Event has already ended';
        }

        return 'Ticket is valid';
    }
}
