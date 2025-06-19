<?php

namespace App\Http\Controllers\Web\Config;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.pages.config.setting.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $settings = Setting::select(['id', 'key', 'value', 'type', 'group', 'is_public']);

            return DataTables::of($settings)
                ->addColumn('action', function ($row) {
                    return '
                        <div class="d-flex justify-content-center">
                             <button class="btn btn-warning open-global-modal me-1" data-url="' . route('config.setting.edit', $row->id) . '"
                                data-title="Edit Setting">
                                 <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-global-delete" data-url="' . route('config.setting.destroy', $row->id) . '">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    ';
                })
                ->editColumn('is_public', fn($row) => $row->is_public ? 'Yes' : 'No')
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function create()
    {
        return view('admin.pages.config.setting.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|unique:settings,key',
            'value' => 'nullable',
            'type' => 'required|in:string,integer,boolean,json',
            'description' => 'nullable|string',
            'group' => 'required|string',
            'is_public' => 'nullable|boolean',
        ]);

        $value = $request->input('value');

        switch ($validated['type']) {
            case 'integer':
                $value = (int) $value;
                break;

            case 'boolean':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;

            case 'json':
                json_decode($value);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'message' => 'Format JSON tidak valid.',
                        'errors' => ['value' => ['Isi value bukan JSON valid.']]
                    ], 422);
                }
                break;

            case 'string':
            default:
                // do nothing
                break;
        }

        $setting = new Setting();
        $setting->key = $validated['key'];
        $setting->value = is_array($value) ? json_encode($value) : (string) $value;
        $setting->type = $validated['type'];
        $setting->description = $request->input('description');
        $setting->group = $validated['group'];
        $setting->is_public = $request->boolean('is_public', false);
        $setting->save();

        return response()->json(['message' => 'Setting berhasil ditambahkan.']);
    }

    public function edit($id)
    {
        $setting = Setting::findOrFail($id);
        return view('admin.pages.config.setting.edit', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);

        $validated = $request->validate([
            'key' => 'required|unique:settings,key,' . $id,
            'value' => 'nullable',
            'type' => 'required|in:string,integer,boolean,json',
            'description' => 'nullable|string',
            'group' => 'required|string',
            'is_public' => 'nullable|boolean',
        ]);

        $value = $request->input('value');

        switch ($validated['type']) {
            case 'integer':
                $value = (int) $value;
                break;

            case 'boolean':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;

            case 'json':
                json_decode($value);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'message' => 'Format JSON tidak valid.',
                        'errors' => ['value' => ['Isi value bukan JSON valid.']]
                    ], 422);
                }
                break;

            case 'string':
            default:
                // do nothing
                break;
        }

        $setting->key = $validated['key'];
        $setting->value = is_array($value) ? json_encode($value) : (string) $value;
        $setting->type = $validated['type'];
        $setting->description = $request->input('description');
        $setting->group = $validated['group'];
        $setting->is_public = $request->boolean('is_public', false);
        $setting->save();

        return response()->json(['message' => 'Setting berhasil diperbarui.']);
    }


    public function destroy($id)
    {
        Setting::findOrFail($id)->delete();
        return response()->json(['message' => 'Setting berhasil dihapus.']);
    }
}
