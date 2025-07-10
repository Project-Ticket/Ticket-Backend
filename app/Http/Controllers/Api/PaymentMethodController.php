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

    public function show($code)
    {
        $paymentMethod = PaymentMethod::active()->where('code', $code)->firstOrFail();

        return MessageResponseJson::success('success get payment methods', $paymentMethod);
    }
}
