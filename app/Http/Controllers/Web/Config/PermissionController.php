<?php

namespace App\Http\Controllers\Web\Config;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;

class PermissionController extends Controller
{
    protected $permission;

    public function __construct()
    {
        $this->permission = new Permission();
    }

    public function index()
    {
        return view('admin.pages.config.permission.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->permission->select(['id', 'name', 'guard_name'])->latest();

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '
                    <div class="d-flex justify-content-center">
                        <button class="btn btn-danger btn-global-delete"
                            data-url="' . route('config.permission.destroy', $row->id) . '"
                            title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function create(Request $request)
    {
        return view('admin.pages.config.permission.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions,name',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError($validator->errors()->first());
        }
        try {
            $this->permission->create([
                'name' => $request->name,
            ]);
            DB::commit();
            return MessageResponseJson::success('Permission berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return MessageResponseJson::serverError('Terjadi kesalahan saat menyimpan permission: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            DB::table($this->permission->getTable())->where('id', $id)->delete();

            DB::commit();

            return MessageResponseJson::success('Permission berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return MessageResponseJson::serverError('Terjadi kesalahan saat menghapus permission: ' . $e->getMessage());
        }
    }
}
