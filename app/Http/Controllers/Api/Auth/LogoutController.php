<?php

namespace App\Http\Controllers\Api\Auth;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class LogoutController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $token = $request->cookie('auth_token');

            if ($token) {
                $tokenData = PersonalAccessToken::findToken($token);
                if ($tokenData) {
                    $tokenData->delete();
                }
            }

            Cookie::queue(Cookie::forget('auth_token'));

            DB::commit();

            return MessageResponseJson::success('Logout successful');
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Something went wrong during logout', [$th->getMessage()]);
        }
    }
}
