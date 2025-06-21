<?php

namespace App\Http\Controllers\Web;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
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
        if ($request->ajax()) {
            $organizers = EventOrganizer::with('user')->select('event_organizers.*');

            return DataTables::of($organizers)
                ->addIndexColumn()
                ->editColumn('organization_name', function ($row) {
                    return '<a href="javascript:void(0)" class="fw-bold text-primary link-under-review" data-uuid="' . $row->uuid . '" data-url="' . route('event-organizer.show', $row->uuid) . '">' . $row->organization_name . '</a>';
                })

                ->addColumn('owner_name', fn($row) => $row->user->name ?? '-')
                ->addColumn('owner_email', fn($row) => $row->user->email ?? '-')
                ->addColumn('owner_phone', fn($row) => $row->user->phone ?? '-')
                ->editColumn('status', fn($row) => Status::getBadgeHtml('userStatus', $row->status))
                ->editColumn('verification_status', fn($row) => $row->verification_status)
                ->editColumn('created_at', fn($row) => $row->created_at->format('d M Y'))
                ->addColumn('action', function ($row) {
                    return '
                    <div class="d-flex justify-content-center">
                        <button class="btn btn-danger btn-global-delete" data-url="' . route('event-organizer.destroy', $row->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                ';
                })
                ->rawColumns(['organization_name', 'status', 'verification_status', 'action'])
                ->make(true);
        }
    }

    public function markUnderReview($uuid)
    {
        $organizer = EventOrganizer::where('uuid', $uuid)->firstOrFail();

        // Update hanya jika masih 'pending'
        if ($organizer->application_status === 'pending') {
            $organizer->application_status = 'under_review';
            $organizer->reviewed_by = Auth::user()->id;
            $organizer->reviewed_at = now();
            $organizer->save();
        }

        return response()->json(['success' => true]);
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
            'status_type'  => 'required|in:application_status,verification_status',
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

            // Tambahan jika kamu juga ingin meng-handle verification_status
            if ($request->status_type === 'verification_status') {
                $organizer->verification_status = $request->status_value;
                $organizer->verification_notes  = $request->status_value === 'rejected' ? $request->rejection_reason : null;
                $organizer->verified_at         = now();
            }

            $organizer->save();
            DB::commit();

            return back()->with('success', 'Status berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memperbarui status.');
        }
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
}
