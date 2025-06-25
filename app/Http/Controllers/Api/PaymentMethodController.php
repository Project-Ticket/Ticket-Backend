<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    protected $paymentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
    }

    public function index(Request $request)
    {
        $type = $request->query('type');
        $paymentMethods = $this->paymentService->getAvailablePaymentMethods($type);

        return response()->json([
            'success' => true,
            'data' => $paymentMethods
        ]);
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
}
