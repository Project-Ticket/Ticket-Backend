<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    protected $paymentMethod;

    public function __construct()
    {
        $this->paymentMethod = new PaymentMethod();
    }

    public function index(Request $request)
    {
        $type = $request->input('type');

        $query = PaymentMethod::active()->orderBy('sort_order');

        if ($type) {
            $query->where('type', $type);
        }

        $paymentMethods = $query->get();

        return MessageResponseJson::success('success get payment methods', $paymentMethods);
    }

    public function calculateFee(Request $request)
    {
        $request->validate([
            'payment_method_code'   => 'required|string|exists:payment_methods,code',
            'amount'                => 'required|numeric|min:0'
        ]);

        try {
            $fee = $this->paymentService->calculatePaymentFee(
                $request->payment_method_code,
                $request->amount
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'fee' => $fee,
                    'total_amount' => $request->amount + $fee
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show($code)
    {
        $paymentMethod = PaymentMethod::active()->where('code', $code)->firstOrFail();

        return MessageResponseJson::success('success get payment methods', $paymentMethod);
    }
}
