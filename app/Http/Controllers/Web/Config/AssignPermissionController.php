<?php

namespace App\Http\Controllers\Web\Config;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;

class AssignPermissionController extends Controller
{
    protected $user, $permission;

    public function __construct()
    {
        $this->user = new User();
        $this->permission = new Permission();
    }

    public function index()
    {
        return view('admin.pages.config.assign-permission.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->user->select(['id', 'name', 'email', 'status', 'created_at']);
            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '
                    <div class="d-flex justify-content-center">
                        <a href="' . route('config.assign.create', $row->id) . '" class="btn btn-info me-1" title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                    ';
                })
                ->editColumn('status', fn($row) => Status::getBadgeHtml('userStatus', $row->status))
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
    }

    public function create($id)
    {
        $user = $this->user->findOrFail($id);
        $allPermissions = $this->permission->all();
        $assignedPermissions = $user->getAllPermissions();
        $assignedIds = $assignedPermissions->pluck('id')->toArray();

        $availablePermissions = $allPermissions->whereNotIn('id', $assignedIds);
        return view('admin.pages.config.assign-permission.create', compact('user', 'assignedPermissions', 'availablePermissions'));
    }

    public function assignPermission(Request $request)
    {
        DB::beginTransaction();
        $user = $this->user->findOrFail($request->id);

        try {
            $user->givePermissionTo($request->permissions);
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function revokePermission(Request $request)
    {
        DB::beginTransaction();
        $user = $this->user->findOrFail($request->id);

        try {
            $user->revokePermissionTo($request->permissions);
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => false, 'message' => $th->getMessage()], 500);
        }
    }
}
