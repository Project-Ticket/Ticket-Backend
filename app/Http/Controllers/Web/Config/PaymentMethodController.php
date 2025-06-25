<?php

namespace App\Http\Controllers\Web\Config;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PaymentMethodController extends Controller
{
    protected $paymentMethod;

    public function __construct()
    {
        $this->paymentMethod = new PaymentMethod();
    }
    public function index()
    {
        return view('admin.pages.config.payment-method.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->paymentMethod->select(['id', 'code', 'name', 'type', 'is_active', 'fee_percentage', 'fee_fixed'])->latest();

            return DataTables::of($data)
                ->addColumn('status', function ($row) {
                    return '
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input status-toggle" type="checkbox" id="flexSwitchCheck_' . $row->id . '" data-id="' . $row->id . '"' . ($row->is_active ? ' checked' : '') . ' />
                    <label class="form-check-label" for="flexSwitchCheck_' . $row->id . '">' . ($row->is_active ? 'Active' : 'Inactive') . '</label>
                </div>';
                })
                ->addColumn('action', function ($row) {
                    $editButton = '
                <button class="btn btn-warning btn-global-edit me-2"
                    data-url="' . route('config.payment-method.edit', $row->id) . '"
                    title="Edit">
                    <i class="fas fa-edit"></i>
                </button>';

                    $deleteButton = '
                <button class="btn btn-danger btn-global-delete me-2"
                    data-url="' . route('config.payment-method.destroy', $row->id) . '"
                    title="Delete">
                    <i class="fas fa-trash"></i>
                </button>';

                    return '
                <div class="text-center">
                    ' . $editButton . '
                    ' . $deleteButton . '
                </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    }

    public function create(Request $request)
    {
        return view('admin.pages.config.payment-method.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'code' => 'required|unique:payment_methods,code',
            'name' => 'required',
            'type' => 'required',
            'fee_percentage' => 'required|numeric',
            'fee_fixed' => 'required|numeric',
            'minimum_fee' => 'required|numeric',
            'maximum_fee' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError($validator->errors()->first());
        }
        try {
            $this->paymentMethod->create($request->all());
            DB::commit();
            return MessageResponseJson::success('Payment method berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return MessageResponseJson::serverError('Terjadi kesalahan saat menyimpan payment method: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $paymentMethod = $this->paymentMethod->findOrFail($id);
        return view('admin.pages.config.payment-method.edit', compact('paymentMethod'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'code' => 'required|unique:payment_methods,code,' . $id,
            'name' => 'required',
            'type' => 'required',
            'fee_percentage' => 'required|numeric',
            'fee_fixed' => 'required|numeric',
            'minimum_fee' => 'required|numeric',
            'maximum_fee' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError($validator->errors()->first());
        }

        try {
            $paymentMethod = $this->paymentMethod->findOrFail($id);
            $paymentMethod->update($request->all());
            DB::commit();
            return MessageResponseJson::success('Payment method berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return MessageResponseJson::serverError('Terjadi kesalahan saat memperbarui payment method: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            DB::table($this->paymentMethod->getTable())->where('id', $id)->delete();

            DB::commit();

            return MessageResponseJson::success('Payment method berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return MessageResponseJson::serverError('Terjadi kesalahan saat menghapus payment method: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $paymentMethod = $this->paymentMethod->findOrFail($id);
        $newStatus = $request->input('is_active') == 'true';

        DB::beginTransaction();

        try {
            $hasActiveOrders = DB::table('orders')->where('payment_method', $paymentMethod->code)->where('status', '!=', 'canceled')->exists();

            if ($hasActiveOrders && !$newStatus) {
                return MessageResponseJson::validationError('Cannot deactivate this payment method. It is associated with active orders.');
            }

            $paymentMethod->is_active = $newStatus;
            $paymentMethod->save();

            DB::commit();
            return MessageResponseJson::success('Status berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return MessageResponseJson::serverError('Terjadi kesalahan saat memperbarui status: ' . $e->getMessage());
        }
    }
}
