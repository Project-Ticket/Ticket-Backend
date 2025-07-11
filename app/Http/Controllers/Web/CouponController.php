<?php

namespace App\Http\Controllers\Web;

use App\Facades\MessageResponseJson;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\EventOrganizer;
use App\Models\Merchandise;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CouponController extends Controller
{
    public function index()
    {
        return view('admin.pages.coupon.index');
    }

    public function getData(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        $coupons = Coupon::with('organizer')->select('coupons.*');

        return DataTables::of($coupons)
            ->addIndexColumn()
            ->editColumn('value', function ($row) {
                return $row->type === 'percentage'
                    ? $row->value . '%'
                    : 'Rp ' . number_format($row->value, 0, ',', '.');
            })
            ->editColumn('valid_from', fn($row) => Carbon::parse($row->valid_from)->format('d M Y H:i'))
            ->editColumn('valid_until', fn($row) => Carbon::parse($row->valid_until)->format('d M Y H:i'))
            ->editColumn('status', function ($row) {
                $isActive = $row->is_active &&
                    now()->between($row->valid_from, $row->valid_until);

                return $isActive
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
            <div class="d-flex justify-content-center gap-1">
                <button class="btn btn-primary open-global-modal" data-url="' . route('coupon.show', $row->id) . '" data-title="Detail Coupon">
                    <i class="fas fa-eye"></i>
                </button>
                <button data-url="' . route('coupon.destroy', $row->id) . '"
                        class="btn btn-danger btn-global-delete" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $data =  [
            'organizers' => EventOrganizer::where('status', EventOrganizer::STATUS_ACTIVE)->get(),
            'events' => Event::where('status', Event::STATUS_PUBLISHED)->get(),
            'merchandises' => Merchandise::where('is_active', true)->get(),
        ];
        return view('admin.pages.coupon.create', $data);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'code' => 'required|unique:coupons,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed_amount',
            'value' => 'required|numeric|min:0',
            'organizer_id' => 'nullable|exists:event_organizers,id',
            'description' => 'nullable|string',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_limit_per_user' => 'nullable|integer|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'applicable_to' => 'required|in:tickets,merchandise,both',
            'applicable_events' => 'nullable|array',
            'applicable_merchandise' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError($validator->errors()->first());
        }

        try {
            $couponData = [
                'code' => $request->code,
                'name' => $request->name,
                'type' => $request->type,
                'value' => $request->value,
                'organizer_id' => $request->organizer_id,
                'description' => $request->description,
                'minimum_amount' => $request->minimum_amount,
                'maximum_discount' => $request->maximum_discount,
                'usage_limit' => $request->usage_limit,
                'usage_limit_per_user' => $request->usage_limit_per_user,
                'valid_from' => $request->valid_from,
                'valid_until' => $request->valid_until,
                'applicable_to' => $request->applicable_to,
                'applicable_events' => $request->applicable_events ? json_encode($request->applicable_events) : null,
                'applicable_merchandise' => $request->applicable_merchandise ? json_encode($request->applicable_merchandise) : null,
                'is_active' => $request->has('is_active'),
                'used_count' => 0
            ];

            $coupon = Coupon::create($couponData);

            DB::commit();
            return MessageResponseJson::success('Coupon berhasil ditambahkan.', $coupon);
        } catch (\Throwable $e) {
            DB::rollBack();
            return MessageResponseJson::serverError('Gagal menyimpan coupon: ' . $e->getMessage());
        }
    }

    public function generateCouponCode()
    {
        do {
            $code = Helper::generateRandomCode();
        } while (Coupon::where('code', $code)->exists());

        return response()->json(['code' => $code]);
    }

    public function show($id)
    {
        $coupon = Coupon::with('organizer')->findOrFail($id);

        $applicableEvents = $coupon->applicable_events
            ? Event::whereIn('id', json_decode($coupon->applicable_events))->pluck('title')->toArray()
            : [];

        $applicableMerchandise = $coupon->applicable_merchandise
            ? Merchandise::whereIn('id', json_decode($coupon->applicable_merchandise))->pluck('name')->toArray()
            : [];

        $isActive = $coupon->is_active &&
            now()->between($coupon->valid_from, $coupon->valid_until);

        return view('admin.pages.coupon.show', [
            'coupon' => $coupon,
            'applicableEvents' => $applicableEvents,
            'applicableMerchandise' => $applicableMerchandise,
            'isActive' => $isActive
        ]);
    }

    public function destroy($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coupon berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus coupon: ' . $e->getMessage()
            ], 500);
        }
    }
}
