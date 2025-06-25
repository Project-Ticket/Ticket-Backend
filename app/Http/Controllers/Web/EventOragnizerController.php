<?php

namespace App\Http\Controllers\Web;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventOrganizer;
use App\Models\User;
use App\Services\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class EventOragnizerController extends Controller
{
    public function index()
    {
        return view('admin.pages.event-organizer.index');
    }

    public function getData(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        $organizers = EventOrganizer::with('user')->select('event_organizers.*');

        return DataTables::of($organizers)
            ->addIndexColumn()
            ->editColumn('organization_name', function ($row) {
                return '<a href="javascript:void(0)" class="fw-bold text-primary link-under-review" data-uuid="' . $row->uuid . '" data-url="' . route('event-organizer.show', $row->uuid) . '">' . e($row->organization_name) . '</a>';
            })
            ->addColumn('owner_name', fn($row) => optional($row->user)->name ?? '-')
            ->addColumn('owner_email', fn($row) => optional($row->user)->email ?? '-')
            ->addColumn('owner_phone', fn($row) => optional($row->user)->phone ?? '-')
            ->editColumn('status', fn($row) => Status::getBadgeHtml('userStatus', $row->status))
            ->editColumn('verification_status', function ($row) {
                if ($row->verification_status === 'pending') {
                    return '<span class="badge bg-warning text-dark">Pending</span>';
                } elseif ($row->verification_status === 'verified') {
                    return '<span class="badge bg-success">Verified</span>';
                } elseif ($row->verification_status === 'rejected') {
                    return '<span class="badge bg-danger">Rejected</span>';
                } else {
                    return '<span class="badge bg-secondary">Unknown</span>';
                }
            })
            ->editColumn('created_at', fn($row) => $row->created_at->format('d M Y'))
            ->addColumn('action', function ($row) {
                return '
                <div class="d-flex justify-content-center gap-1">
                    <a href="' . route('event-organizer.show-events', $row->uuid) . '" class="btn btn-info" title="Lihat Events">
                        <i class="fas fa-list"></i>
                    </a>
                    <button class="btn btn-danger btn-global-delete" data-url="' . route('event-organizer.destroy', $row->id) . '" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            ';
            })
            ->rawColumns(['organization_name', 'status', 'verification_status', 'action'])
            ->make(true);
    }

    public function show($uuid)
    {
        $organizer = EventOrganizer::with(['user'])->where('uuid', $uuid)->firstOrFail();

        if (
            $organizer->application_status === 'under_review' &&
            $organizer->reviewed_by !== Auth::id()
        ) {
            return redirect()->back()->with('error', 'Aplikasi ini sedang direview oleh pengguna lain.');
        }

        return view('admin.pages.event-organizer.show', compact('organizer'));
    }

    public function destroy($id)
    {
        $organizer = EventOrganizer::findOrFail($id);
        $user = User::findOrFail($organizer->user_id);

        DB::beginTransaction();
        try {
            $organizer->delete();
            $user->delete();

            DB::commit();
            return MessageResponseJson::success('Event Organizer and associated user deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to delete Event Organizer: ' . $e->getMessage());
        }
    }

    public function showEventDetails($id)
    {
        $event = Event::with(['registrations', 'ticketTypes'])->findOrFail($id);
        return view('admin.pages.event-organizer.event-detail-modal', compact('event'));
    }

    public function showParticipants($id)
    {
        $event = Event::with('registrations.user')->findOrFail($id);
        return view('admin.pages.event-organizer.participant-list-modal', compact('event'));
    }

    public function showEvents(Request $request, $uuid)
    {
        $organizer = EventOrganizer::where('uuid', $uuid)
            ->with(['user', 'events' => function ($query) use ($request) {
                $query->with(['ticketTypes' => function ($q) {
                    $q->where('is_active', true)->orderBy('sort_order');
                }, 'category', 'registrations']);

                if ($request->filled('status') && $request->status !== 'all') {
                    $query->where('status', Status::event()->getId($request->status));
                }

                if ($request->filled('search')) {
                    $query->where(function ($q) use ($request) {
                        $q->where('title', 'like', '%' . $request->search . '%')
                            ->orWhere('description', 'like', '%' . $request->search . '%')
                            ->orWhere('venue_name', 'like', '%' . $request->search . '%');
                    });
                }

                $query->orderBy('created_at', 'desc');
            }])
            ->firstOrFail();

        $eventsQuery = $organizer->events()
            ->with(['ticketTypes' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }, 'category', 'registrations']);

        if ($request->filled('status') && $request->status !== 'all') {
            $eventsQuery->where('status', Status::event()->getId($request->status));
        }

        if ($request->filled('search')) {
            $eventsQuery->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('venue_name', 'like', '%' . $request->search . '%');
            });
        }

        $events = $eventsQuery->withCount('registrations')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalEvents = $organizer->events()->count();
        $activeEvents = $organizer->events()->where('status', Status::getId('eventStatus', 'PUBLISHED'))->count();
        $upcomingEvents = $organizer->events()
            ->where('start_datetime', '>', now())
            ->whereIn('status', [1, 2])
            ->count();

        $totalParticipants = $organizer->events()
            ->withCount('registrations')
            ->get()
            ->sum('registrations_count');

        return view('admin.pages.event-organizer.event', compact(
            'organizer',
            'events',
            'totalEvents',
            'activeEvents',
            'upcomingEvents',
            'totalParticipants'
        ));
    }

    public function rejectVerification($uuid)
    {
        $organizer = EventOrganizer::where('uuid', $uuid)->firstOrFail();
        return view('admin.pages.event-organizer.reject-verification-modal', compact('organizer'));
    }

    public function rejectApplication($uuid)
    {
        $organizer = EventOrganizer::where('uuid', $uuid)->firstOrFail();
        return view('admin.pages.event-organizer.reject-application-modal', compact('organizer'));
    }

    public function updateStatus(Request $request, $uuid)
    {
        $request->validate([
            'status_type'  => 'required|in:application_status,verification_status,status',
            'status_value' => 'required|string',
            'rejection_reason' => 'required_if:status_value,rejected|string|nullable',
        ]);

        try {
            DB::beginTransaction();

            $organizer = EventOrganizer::where('uuid', $uuid)->first();

            if (!$organizer) {
                return back()->with('error', 'Event Organizer tidak ditemukan.');
            }

            if ($request->status_type === 'application_status') {
                $organizer->application_status = $request->status_value;
                $organizer->rejection_reason   = $request->status_value === 'rejected' ? $request->rejection_reason : null;
                $organizer->reviewed_by        = Auth::user()->id;
                $organizer->reviewed_at        = now();
            }

            if ($request->status_type === 'verification_status') {
                $organizer->verification_status = $request->status_value;
                $organizer->verification_notes  = $request->status_value === 'rejected' ? $request->rejection_reason : null;
                $organizer->verified_at         = now();
            }

            $status = (int) $request->status_value;
            if ($request->status_type === 'status') {
                $organizer->status = $status;
            } else {
                $organizer->status = 0;
            }

            $organizer->save();

            DB::commit();

            return back()->with('success', 'Status berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memperbarui status.');
        }
    }
    public function markUnderReview($uuid)
    {
        $organizer = EventOrganizer::where('uuid', $uuid)->firstOrFail();

        if ($organizer->application_status === 'pending') {
            $organizer->application_status = 'under_review';
            $organizer->reviewed_by = Auth::user()->id;
            $organizer->reviewed_at = now();
            $organizer->save();
        }

        return response()->json(['success' => true]);
    }
}
