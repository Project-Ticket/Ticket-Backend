<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventOrganizer;
use App\Services\Status;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EventController extends Controller
{
    public function index()
    {
        return view('admin.pages.event.index');
    }

    public function getData(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        $events = Event::with('organizer', 'category')->select('events.*');

        return DataTables::of($events)
            ->addIndexColumn()
            ->addColumn('organizer_name', function ($row) {
                return optional($row->organizer)->organization_name ?? '-';
            })
            ->editColumn('start_datetime', fn($row) => $row->start_datetime->format('d M Y H:i'))
            ->editColumn('end_datetime', fn($row) => $row->end_datetime->format('d M Y H:i'))
            ->editColumn('status', function ($row) {
                // Gunakan Status helper untuk mendapatkan badge
                return Status::getBadgeHtml('eventStatus', $row->status);
            })
            ->addColumn('action', function ($row) {
                return '
            <div class="d-flex justify-content-center gap-1">
                <a href="' . route('event.show', $row->id) . '" class="btn btn-info" title="Lihat">
                    <i class="fas fa-eye"></i>
                </a>
            </div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $event = Event::with(['organizer', 'category'])->findOrFail($id);
        return view('admin.pages.event.show', compact('event'));
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json(['message' => 'Event berhasil dihapus']);
    }
}
