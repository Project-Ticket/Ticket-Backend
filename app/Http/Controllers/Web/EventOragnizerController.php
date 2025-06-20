<?php

namespace App\Http\Controllers\Web;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\EventOrganizer;
use App\Models\User;
use App\Services\Status;
use Illuminate\Http\Request;
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
                ->addColumn('owner_name', fn($row) => $row->user->name ?? '-')
                ->addColumn('owner_email', fn($row) => $row->user->email ?? '-')
                ->addColumn('owner_phone', fn($row) => $row->user->phone ?? '-')
                ->editColumn('status', fn($row) => Status::getBadgeHtml('userStatus', $row->status))
                ->editColumn('verification_status', fn($row) => $row->verification_status)
                ->editColumn('created_at', fn($row) => $row->created_at->format('d M Y'))
                ->addColumn('action', function ($row) {
                    return '
                    <div class="d-flex justify-content-center">
                        <button class="btn btn-warning open-global-modal me-1" data-url="' . url('event-organizer.edit', $row->id) . '" data-title="Edit Event Organizer">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-global-delete" data-url="' . url('event-organizer.destroy', $row->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                ';
                })
                ->rawColumns(['status', 'verification_status', 'action'])
                ->make(true);
        }
    }
}
