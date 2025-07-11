<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\EventOrganizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    protected $coupon, $eventOrganizer;

    public function __construct()
    {
        $this->coupon = new Coupon();
        $this->eventOrganizer = new EventOrganizer();
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->coupon->query();

            $eventOrganizer = $this->eventOrganizer
                ->where('user_id', Auth::id())
                ->first();

            if (!$eventOrganizer) {
                return MessageResponseJson::forbidden(
                    'You are not registered as an event organizer'
                );
            }

            $query->where('organizer_id', $eventOrganizer->id);

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('code', 'like', '%' . $request->search . '%');
                });
            }

            $perPage = $request->get('per_page', 10);

            $coupons = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return MessageResponseJson::paginated(
                'Coupons retrieved successfully',
                $coupons
            );
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError(
                'Failed to fetch coupons',
                ['error' => $th->getMessage()]
            );
        }
    }

    public function store(Request $request): JsonResponse
    {
        $eventOrganizer = $this->eventOrganizer
            ->where('user_id', Auth::id())
            ->first();

        if (!$eventOrganizer) {
            return MessageResponseJson::forbidden(
                'You are not registered as an event organizer'
            );
        }

        $validator = Validator::make($request->all(), [
            'code'                      => 'required|unique:coupons,code',
            'name'                      => 'required|string|max:255',
            'description'               => 'nullable|string',
            'type'                      => 'required|in:percentage,fixed_amount',
            'value'                     => 'required|numeric|min:0',
            'minimum_amount'            => 'nullable|numeric|min:0',
            'maximum_discount'          => 'nullable|numeric|min:0',
            'usage_limit'               => 'nullable|integer|min:0',
            'usage_limit_per_user'      => 'nullable|integer|min:0',
            'valid_from'                => 'required|date|after_or_equal:today',
            'valid_until'               => 'required|date|after:valid_from',
            'applicable_to'             => 'required|in:tickets,merchandise,both',
            'applicable_events'         => 'nullable|array',
            'applicable_merchandise'    => 'nullable|array',
            'is_active'                 => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        DB::beginTransaction();
        try {
            $coupon = $this->coupon->create([
                'organizer_id'           => $eventOrganizer->id,
                'code'                   => $request->code,
                'name'                   => $request->name,
                'description'            => $request->description,
                'type'                   => $request->type,
                'value'                  => $request->value,
                'minimum_amount'         => $request->minimum_amount,
                'maximum_discount'       => $request->maximum_discount,
                'usage_limit'            => $request->usage_limit,
                'usage_limit_per_user'   => $request->usage_limit_per_user,
                'valid_from'             => $request->valid_from,
                'valid_until'            => $request->valid_until,
                'applicable_to'          => $request->applicable_to,
                'applicable_events'      => $request->applicable_events ? json_encode($request->applicable_events) : null,
                'applicable_merchandise' => $request->applicable_merchandise ? json_encode($request->applicable_merchandise) : null,
                'is_active'              => $request->is_active ?? true,
            ]);

            DB::commit();

            return MessageResponseJson::created(
                'Coupon created successfully',
                $coupon
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError(
                'Failed to create coupon',
                ['error' => $th->getMessage()]
            );
        }
    }

    public function show(string $uuid): JsonResponse
    {
        try {
            $eventOrganizer = $this->eventOrganizer
                ->where('user_id', Auth::id())
                ->first();

            if (!$eventOrganizer) {
                return MessageResponseJson::forbidden(
                    'You are not registered as an event organizer'
                );
            }

            $coupon = $this->coupon
                ->where('uuid', $uuid)
                ->where('organizer_id', $eventOrganizer->id)
                ->first();

            if (!$coupon) {
                return MessageResponseJson::notFound(
                    'Coupon not found or unauthorized'
                );
            }

            return MessageResponseJson::success(
                'Coupon retrieved successfully',
                $coupon
            );
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError(
                'Failed to retrieve coupon',
                ['error' => $th->getMessage()]
            );
        }
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $eventOrganizer = $this->eventOrganizer
            ->where('user_id', Auth::id())
            ->first();

        if (!$eventOrganizer) {
            return MessageResponseJson::forbidden(
                'You are not registered as an event organizer'
            );
        }

        $validator = Validator::make($request->all(), [
            'code'                      => 'nullable|unique:coupons,code,' . $uuid . ',uuid',
            'name'                      => 'nullable|string|max:255',
            'description'               => 'nullable|string',
            'type'                      => 'nullable|in:percentage,fixed_amount',
            'value'                     => 'nullable|numeric|min:0',
            'minimum_amount'            => 'nullable|numeric|min:0',
            'maximum_discount'          => 'nullable|numeric|min:0',
            'usage_limit'               => 'nullable|integer|min:0',
            'usage_limit_per_user'      => 'nullable|integer|min:0',
            'valid_from'                => 'nullable|date|after_or_equal:today',
            'valid_until'               => 'nullable|date|after:valid_from',
            'applicable_to'             => 'nullable|in:tickets,merchandise,both',
            'applicable_events'         => 'nullable|array',
            'applicable_merchandise'    => 'nullable|array',
            'is_active'                 => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        DB::beginTransaction();
        try {
            $coupon = $this->coupon
                ->where('uuid', $uuid)
                ->where('organizer_id', $eventOrganizer->id)
                ->first();

            if (!$coupon) {
                return MessageResponseJson::notFound(
                    'Coupon not found or unauthorized'
                );
            }

            $updateData = array_filter([
                'code'                   => $request->code,
                'name'                   => $request->name,
                'description'            => $request->description,
                'type'                   => $request->type,
                'value'                  => $request->value,
                'minimum_amount'         => $request->minimum_amount,
                'maximum_discount'       => $request->maximum_discount,
                'usage_limit'            => $request->usage_limit,
                'usage_limit_per_user'   => $request->usage_limit_per_user,
                'valid_from'             => $request->valid_from,
                'valid_until'            => $request->valid_until,
                'applicable_to'          => $request->applicable_to,
                'applicable_events'      => $request->applicable_events ? json_encode($request->applicable_events) : null,
                'applicable_merchandise' => $request->applicable_merchandise ? json_encode($request->applicable_merchandise) : null,
                'is_active'              => $request->is_active,
            ]);

            $coupon->update($updateData);

            DB::commit();

            return MessageResponseJson::success(
                'Coupon updated successfully',
                $coupon->fresh()
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError(
                'Failed to update coupon',
                ['error' => $th->getMessage()]
            );
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        try {
            $eventOrganizer = $this->eventOrganizer
                ->where('user_id', Auth::id())
                ->first();

            if (!$eventOrganizer) {
                return MessageResponseJson::forbidden(
                    'You are not registered as an event organizer'
                );
            }

            $coupon = $this->coupon
                ->where('uuid', $uuid)
                ->where('organizer_id', $eventOrganizer->id)
                ->first();

            if (!$coupon) {
                return MessageResponseJson::notFound(
                    'Coupon not found or unauthorized'
                );
            }

            $coupon->delete();

            return MessageResponseJson::success('Coupon deleted successfully');
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError(
                'Failed to delete coupon',
                ['error' => $th->getMessage()]
            );
        }
    }

    public function generateCouponCode()
    {
        do {
            $code = Helper::generateRandomCode();
        } while (Coupon::where('code', $code)->exists());

        return response()->json(['code' => $code]);
    }
}
