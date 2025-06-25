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
                return optional($row->organizer)->organization_name ?? '-'; // Mengambil organizer_name
            })
            ->editColumn('start_datetime', fn($row) => $row->start_datetime->format('d M Y H:i'))
            ->editColumn('end_datetime', fn($row) => $row->end_datetime->format('d M Y H:i'))
            ->editColumn('status', fn($row) => $row->is_featured ? '<span class="badge bg-success">Featured</span>' : '<span class="badge bg-secondary">Regular</span>')
            ->addColumn('action', function ($row) {
                return '
                <div class="d-flex justify-content-center gap-1">
                    <a href="' . url('events.show', $row->id) . '" class="btn btn-info" title="Lihat">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button class="btn btn-danger btn-global-delete" data-url="' . url('events.destroy', $row->id) . '" title="Hapus">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $event = Event::with(['organizer', 'category'])->findOrFail($id);
        return view('admin.pages.events.show', compact('event'));
    }

    // Menghapus event
    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json(['message' => 'Event berhasil dihapus']);
    }
}
