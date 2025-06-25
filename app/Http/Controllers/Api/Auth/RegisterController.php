<?php

namespace App\Http\Controllers\Api\Auth;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\VerifyEmail;
use App\Services\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function register(Request $request): JsonResponse
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6|confirmed',
            'phone'       => 'nullable|string|max:20',
            'birth_date'  => 'nullable|date',
            'gender'      => ['nullable', Rule::in(['male', 'female', 'other'])],
            'address'     => 'nullable|string',
            'city'        => 'nullable|string',
            'province'    => 'nullable|string',
            'postal_code' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(
                errors: $validator->errors()->toArray()
            );;
        }

        try {
            $user = $this->user->create([
                'uuid'        => Str::uuid(),
                'name'        => $request->name,
                'email'       => $request->email,
                'password'    => Hash::make($request->password),
                'phone'       => $request->phone,
                'birth_date'  => $request->birth_date,
                'gender'      => $request->gender,
                'address'     => $request->address,
                'city'        => $request->city,
                'province'    => $request->province,
                'postal_code' => $request->postal_code,
                'status'      => Status::getId('userStatus', 'ACTIVE'),
            ]);

            $user->assignRole('User');

            // $user->sendEmailVerificationNotification();

            DB::commit();

            return MessageResponseJson::success('Registration successful', $user);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Something went wrong during registration', [$th->getMessage()]);
        }
    }

    public function verifyEmail(Request $request, $id, $hash): JsonResponse
    {
        $user = $this->user->findOrFail($id);

        if (! hash_equals(sha1($user->email), $hash)) {
            return MessageResponseJson::forbidden('Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return MessageResponseJson::forbidden('Email sudah diverifikasi.');
        }

        $user->markEmailAsVerified();

        // return redirect(env('FRONTEND_URL'));
        return MessageResponseJson::success(
            'Email berhasil diverifikasi.',
            $user,
        );
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return MessageResponseJson::notFound('User tidak ditemukan.');
        }

        if ($user->hasVerifiedEmail()) {
            return MessageResponseJson::notFound('Email sudah diverifikasi.');
        }

        $user->notify(new VerifyEmail());

        return MessageResponseJson::success(
            'Email verifikasi berhasil dikirim.',
            $user,
        );
    }
}
