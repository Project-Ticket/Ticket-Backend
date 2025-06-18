<?php

namespace App\Http\Controllers\Api\Auth;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogoutController extends Controller
{
    public function __invoke(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->user()->currentAccessToken()->delete();

            DB::commit();

            return MessageResponseJson::success('Logout successful');
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Something went wrong during logout', [$th->getMessage()]);
        }
    }
}
