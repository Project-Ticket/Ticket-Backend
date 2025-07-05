<?php

namespace App\Http\Controllers\Api\Auth;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = new User();
    }
    public function login(Request $request): JsonResponse
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError('Validation failed', [$validator->errors()->first()]);
        }
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return MessageResponseJson::unauthorized('Email or password is incorrect');
            }

            // if (!$user->hasVerifiedEmail()) {
            //     return MessageResponseJson::unauthorized('Your account is not verified');
            // }

            if ($user->status !== Status::getId('userStatus', 'ACTIVE')) {
                return MessageResponseJson::unauthorized('Your account is not active');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            // Cookie::queue(cookie(
            //     'auth_token',
            //     $token,
            //     60 * 24 * 7, // 7 hari
            //     null,
            //     null,
            //     true,
            //     true,
            //     false,
            //     'Strict' // secure, httpOnly, raw, sameSite
            // ));

            return response()->json([
                'success' => true,
                'code' => 200,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ])->withCookie(cookie(
                'auth_token',
                $token,
                60 * 24 * 7, // 7 days
                null,
                null,
                false, //set true for HTTPS set false for HTTP
                true,
                false
            ));
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Something went wrong', [$th->getMessage()]);
        }
    }

    public function checkAuth(Request $request): JsonResponse
    {
        try {
            $token = $request->cookie('auth_token');

            if (!$token) {
                return MessageResponseJson::unauthorized();
            }

            $tokenData = PersonalAccessToken::findToken($token);

            if (!$tokenData) {
                return MessageResponseJson::unauthorized();
            }

            $user = $tokenData->tokenable;

            if (!$user) {
                return MessageResponseJson::unauthorized();
            }

            $eventOrganizer = $user->eventOrganizer()
                ->where('status', 1)
                ->where('application_status', 'approved')
                ->where('verification_status', 'verified')
                ->first();

            $user->is_event_organizer = $eventOrganizer !== null;
            $user->appliaction_status_organizer = $eventOrganizer?->application_status;
            $user->verification_status_organizer = $eventOrganizer?->verification_status;
            $user->organizer_id = $eventOrganizer?->id;

            return MessageResponseJson::success('Token is valid', [
                'user' => $user,
            ]);
        } catch (\Throwable $e) {
            return MessageResponseJson::serverError('Authentication check failed: ' . $e->getMessage());
        }
    }
}
