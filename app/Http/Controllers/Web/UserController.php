<?php

namespace App\Http\Controllers\Web;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = new User();
    }
    public function index()
    {
        return view('admin.pages.user-management.user.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $users = User::select([
                'id',
                'uuid',
                'name',
                'email',
                'phone',
                'birth_date',
                'gender',
                'city',
                'status',
                'created_at',
            ])
                ->with('roles')
                ->when(Auth::user()->hasRole('Admin') && !Auth::user()->hasRole('Super Admin'), function ($query) {
                    $query->whereDoesntHave('roles', function ($q) {
                        $q->where('name', 'super admin');
                    });
                })
                ->when($request->name, fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
                ->when($request->email, fn($q) => $q->where('email', 'like', '%' . $request->email . '%'))
                ->when($request->phone, fn($q) => $q->where('phone', 'like', '%' . $request->phone . '%'))
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->when($request->role, function ($query) use ($request) {
                    $query->whereHas('roles', function ($q) use ($request) {
                        $q->where('name', $request->role);
                    });
                })
                ->orderBy('id', 'desc');

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '
                        <div class="d-flex justify-content-center">
                            <button class="btn btn-warning open-global-modal me-1" data-url="' . route('user-management.user.edit', $row->uuid) . '" data-title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-global-delete" data-url="' . route('user-management.user.destroy', $row->uuid) . '">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    ';
                })
                ->addColumn('roles', function ($row) {
                    return $row->roles->map(function ($role) {
                        $colors = [
                            'Super Admin'  => 'bg-danger',
                            'Admin' => 'bg-warning',
                            'Organizer' => 'bg-success',
                            'User'  => 'bg-secondary',
                        ];

                        $colorClass = $colors[$role->name] ?? 'bg-dark';

                        return '<span class="badge ' . $colorClass . '">' . e(ucfirst($role->name)) . '</span>';
                    })->implode(' ');
                })
                ->editColumn('name', function ($row) {
                    return '<a href="javascript:void(0)" class="open-global-modal" data-url="' . route('user-management.user.show', $row->uuid) . '">' . e($row->name) . '</a>';
                })
                ->editColumn('status', fn($row) => Status::getBadgeHtml('userStatus', $row->status))

                ->editColumn('gender', function ($row) {
                    return ucfirst($row->gender ?? '-');
                })
                ->editColumn('birth_date', function ($row) {
                    return $row->birth_date ? date('d M Y', strtotime($row->birth_date)) : '-';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? date('d M Y', strtotime($row->created_at)) : '-';
                })
                ->rawColumns(['action', 'status', 'name', 'roles'])
                ->make(true);
        }
    }

    public function create()
    {
        if (Auth::user()->hasRole('Super Admin')) {
            $roles = Role::all();
        } elseif (Auth::user()->hasRole('Admin')) {
            $roles = Role::whereNotIn('name', ['Super Admin', 'Admin'])->get();
        }
        return view('admin.pages.user-management.user.create', compact('roles'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError($validator->errors()->first());
        }

        try {
            $user = User::create([
                'name'        => $request->name,
                'email'       => $request->email,
                'password'    => Hash::make($request->password),
                'phone'       => $request->phone,
                'birth_date'  => $request->birth_date,
                'gender'      => $request->gender,
                'city'        => $request->city,
                'province'    => $request->province,
                'postal_code' => $request->postal_code,
                'address'     => $request->address,
                'status'      => Status::getId('userStatus', 'ACTIVE'),
            ]);

            $user->assignRole($request->role ?? 'User');

            DB::commit();
            return MessageResponseJson::success('User berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return MessageResponseJson::serverError('Gagal menyimpan user: ' . $e->getMessage());
        }
    }

    public function show($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        return view('admin.pages.user-management.user.show', compact('user'));
    }

    public function edit($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        return view('admin.pages.user-management.user.edit', compact('user'));
    }

    public function update(Request $request, $uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError($validator->errors()->first());
        }

        try {
            $user->update([
                'name'        => $request->name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'birth_date'  => $request->birth_date,
                'gender'      => $request->gender,
                'city'        => $request->city,
                'province'    => $request->province,
                'postal_code' => $request->postal_code,
                'address'     => $request->address,
            ]);

            DB::commit();
            return MessageResponseJson::success('User berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return MessageResponseJson::serverError('Gagal update user: ' . $e->getMessage());
        }
    }

    public function destroy($uuid)
    {
        try {
            $user = User::where('uuid', $uuid)->first();

            if (!$user) {
                return MessageResponseJson::notFound('User tidak ditemukan.');
            }

            $user->delete();

            return MessageResponseJson::success('User berhasil dihapus.');
        } catch (\Throwable $e) {
            return MessageResponseJson::serverError('Gagal menghapus user: ' . $e->getMessage());
        }
    }

    public function filter(Request $request)
    {
        if (Auth::user()->hasRole('Super Admin')) {
            $data['roles'] = Role::all();
        } elseif (Auth::user()->hasRole('Admin')) {
            $data['roles'] = Role::whereNotIn('name', ['Super Admin', 'Admin'])->get();
        }

        $data['userStatuses'] = Status::getAll('userStatus');

        return view('admin.pages.user-management.user.filter', $data);
    }
}
