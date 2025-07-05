<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Merchandise;
use App\Models\Wishlist;
use App\Models\Wistlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WhistlistController extends Controller
{
    protected $wishlist, $event, $merchandise;

    public function __construct()
    {
        $this->wishlist = new Wishlist();
        $this->event = new Event();
        $this->merchandise = new Merchandise();
    }

    public function index(Request $request): JsonResponse
    {
        try {
            if (!Auth::user()->hasRole('User')) {
                return MessageResponseJson::forbidden('Unauthorized access');
            }

            $query = $this->wishlist->with([
                'event:id,title,slug,start_datetime,end_datetime,banner_image,venue_name',
                'merchandise:id,name,description,price,image'
            ])->where('user_id', Auth::id());

            if ($request->filled('type')) {
                if ($request->type === 'event') {
                    $query->whereNotNull('event_id');
                } elseif ($request->type === 'merchandise') {
                    $query->whereNotNull('merchandise_id');
                }
            }

            $wishlists = $query->orderByDesc('created_at')
                ->paginate($request->get('per_page', 15));

            return MessageResponseJson::paginated('Wishlist retrieved successfully', $wishlists);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve wishlist', [$th->getMessage()]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        if (!Auth::user()->hasRole('User')) {
            return MessageResponseJson::forbidden('Unauthorized access');
        }

        $validator = Validator::make($request->all(), [
            'event_id' => 'nullable|exists:events,id',
            'merchandise_id' => 'nullable|exists:merchandises,id',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
        }

        if (($request->filled('event_id') && $request->filled('merchandise_id')) ||
            (!$request->filled('event_id') && !$request->filled('merchandise_id'))
        ) {
            return MessageResponseJson::badRequest('Please select either an event or a merchandise, but not both');
        }

        DB::beginTransaction();

        try {
            $userId = Auth::id();

            $existingWishlist = $this->wishlist->where('user_id', $userId)
                ->when($request->event_id, function ($query) use ($request) {
                    return $query->where('event_id', $request->event_id);
                })
                ->when($request->merchandise_id, function ($query) use ($request) {
                    return $query->where('merchandise_id', $request->merchandise_id);
                })
                ->first();

            if ($existingWishlist) {
                return MessageResponseJson::badRequest('Item is already in your wishlist');
            }

            $wishlist = $this->wishlist->create([
                'user_id' => $userId,
                'event_id' => $request->event_id,
                'merchandise_id' => $request->merchandise_id,
            ]);

            $wishlist->load([
                'event:id,title,slug,start_datetime,end_datetime,banner_image',
                'merchandise:id,name,description,price,image'
            ]);

            DB::commit();

            return MessageResponseJson::created('Item added to wishlist successfully', $wishlist);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to add item to wishlist', [$th->getMessage()]);
        }
    }

    public function destroy($id): JsonResponse
    {
        if (!Auth::user()->hasRole('User')) {
            return MessageResponseJson::forbidden('Unauthorized access');
        }

        DB::beginTransaction();

        try {
            $wishlist = $this->wishlist->where('user_id', Auth::id())->findOrFail($id);

            $wishlist->delete();

            DB::commit();

            return MessageResponseJson::success('Item removed from wishlist successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to remove item from wishlist', [$th->getMessage()]);
        }
    }
}
