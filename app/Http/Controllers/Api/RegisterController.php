<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Api\BaseController as BaseController;

class RegisterController extends BaseController
{
    /**
     * Register api
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|max:30',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->sendResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User registered successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Registration Error.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Login api
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->sendResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User logged in successfully.');

        } catch (ValidationException $e) {
            return $this->sendError('Validation Error.', $e->errors());
        }
    }

    /**
     * Logout api
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->sendResponse([], 'User logged out successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Logout Error.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check authentication status
     *
     * @return JsonResponse
     */
    public function checkAuth(): JsonResponse
    {
        return $this->sendResponse(['user' => Auth::user()], 'Authenticated');
    }

    public function getUsers(): JsonResponse
    {
        return response()->json(User::orderBy('id', 'desc')->get(), 200);
    }

    public function updateUser(Request $request, $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'sometimes|required|string|max:30',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user->update($request->only(['name', 'email', 'role']));

        return $this->sendResponse(['user' => $user], 'User updated successfully.');
    }

    /**
     * Delete a user
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteUser($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        $user->delete();

        return $this->sendResponse([], 'User deleted successfully.');
    }

    public function changePassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $user = Auth::user();

            if (!$user) {
                Log::error('Change password attempt for non-existent user');
                return $this->sendError('User not found.', [], 404);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->sendError('Current password is incorrect.', [], 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return $this->sendResponse([], 'Password changed successfully.');
        } catch (\Exception $e) {
            Log::error('Error in changePassword: ' . $e->getMessage());
            return $this->sendError('An unexpected error occurred.', ['error' => $e->getMessage()], 500);
        }
    }
}
