<?php

namespace App\Http\Controllers\Api\Auth;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Status;
use Illuminate\Http\Request;
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
    public function login(Request $request)
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

            return MessageResponseJson::success('Login successful', [
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Something went wrong', [$th->getMessage()]);
        }
    }

    public function checkAuth(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return MessageResponseJson::unauthorized();
            }

            $tokenData = PersonalAccessToken::findToken($token);

            if (!$tokenData) {
                return MessageResponseJson::unauhtorized();
            }

            $user = $tokenData->tokenable;

            if (!$user) {
                return MessageResponseJson::unauthorized();
            }

            $eventOrganizer = $user->eventOrganizer()->first();

            $user->is_event_organizer = $eventOrganizer && $eventOrganizer->status == 1;
            $user->appliaction_status_organizer   = $eventOrganizer ? $eventOrganizer->application_status : null;
            $user->verification_status_organizer = $eventOrganizer ? $eventOrganizer->verification_status : null;

            return MessageResponseJson::success('Token is valid', [
                'user' => $user,
            ]);
        } catch (\Throwable $e) {
            return MessageResponseJson::error('Authentication check failed' . $e->getMessage());
        }
    }
}
