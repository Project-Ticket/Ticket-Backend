<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Services\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function updateProfile(Request $request): JsonResponse
    {
        if (!Auth::user()->hasRole(['User', 'Organizer'])) {
            return MessageResponseJson::forbidden('Access denied. Only users can update their profile.');
        }

        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => 'sometimes|nullable|string|max:20',
            'birth_date' => 'sometimes|nullable|date|before:today',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'address' => 'sometimes|nullable|string|max:1000',
            'city' => 'sometimes|nullable|string|max:100',
            'province' => 'sometimes|nullable|string|max:100',
            'postal_code' => 'sometimes|nullable|string|max:10',
            'avatar' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        try {
            $updateData = $request->only([
                'name',
                'email',
                'phone',
                'birth_date',
                'gender',
                'address',
                'city',
                'province',
                'postal_code',
                'avatar'
            ]);

            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $updateData['avatar'] = $avatarPath;
            }

            $updateData = array_filter($updateData, function ($value) {
                return $value !== null;
            });

            $user->update($updateData);

            $user->refresh();

            return MessageResponseJson::success(
                'Profile updated successfully',
                $user
            );
        } catch (\Exception $e) {
            return MessageResponseJson::serverError(
                'Failed to update profile: ' . $e->getMessage()
            );
        }
    }

    public function updatePassword(Request $request): JsonResponse
    {
        if (!Auth::user()->hasRole(['User', 'Organizer'])) {
            return MessageResponseJson::forbidden('Access denied. Only users can update their password.');
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string'
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return MessageResponseJson::badRequest(
                'Current password is incorrect',
                ['current_password' => ['The current password is incorrect']]
            );
        }

        if (Hash::check($request->password, $user->password)) {
            return MessageResponseJson::badRequest(
                'New password must be different from current password'
            );
        }

        try {
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return MessageResponseJson::success(
                'Password updated successfully'
            );
        } catch (\Exception $e) {
            return MessageResponseJson::serverError(
                'Failed to update password: ' . $e->getMessage()
            );
        }
    }

    public function getProfile(): JsonResponse
    {
        if (!Auth::user()->hasRole(['User', 'Organizer'])) {
            return MessageResponseJson::forbidden('Access denied. Only users can view their profile.');
        }

        $user = Auth::user();
        $statusInfo = Status::getFormatted('userStatus', $user->status, true);

        return MessageResponseJson::success(
            'Profile retrieved successfully',
            [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'phone' => $user->phone,
                'birth_date' => $user->birth_date,
                'gender' => $user->gender,
                'address' => $user->address,
                'city' => $user->city,
                'province' => $user->province,
                'postal_code' => $user->postal_code,
                'avatar' => $user->avatar,
                'status' => [
                    'id' => $statusInfo['id'],
                    'name' => $statusInfo['name'] ?? $user->name,
                    'class' => $user->class,
                    'icon' => $user->icon,
                ],
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'created_at' => $user->created_at,
                'updated_at' => $user->created_at,
                'is_event_organizer' => !is_null($user->eventOrganizer),
                'event_organizer' => $user->eventOrganizer,
            ]
        );
    }
}
