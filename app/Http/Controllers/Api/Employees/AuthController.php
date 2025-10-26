<?php

namespace App\Http\Controllers\Api\Employees;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employees\UpdatePasswordRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Models\Employee;
use App\Traits\HasTranslatedResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Bridge\RefreshToken;

class AuthController extends Controller
{
    use HasTranslatedResponses;
    /**
     * Login employee with email and password
     * Employees authenticate separately from Users using employee guard
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        // Employee where is_active is true, and username or employee_code match
        $employee = Employee::where('is_active', true)
            ->where(function ($query) use ($request) {
                $query->where('email', $request->username)
                    ->orWhere('employee_code', $request->username);
            })
            ->first();

        if (! $employee) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect or the employee is inactive.'],
            ]);
        }

        // If employee has force_password_change flag, allow login without password
        if (! $employee->force_password_change) {
            // Normal authentication - password is required
            if (! $request->password || ! Hash::check($request->password, $employee->password)) {
                throw ValidationException::withMessages([
                    'username' => ['The provided credentials are incorrect.'],
                ]);
            }
        }

        // Create token for employee using employee guard
        $tokenResult = $employee->createToken('employee_token');

        // Get data from the token model
        $accessToken = $tokenResult->accessToken;
        $refreshToken = $tokenResult->accessTokenId;
        $tokenType = $tokenResult->tokenType;
        $expiresIn = $tokenResult->expiresIn;

        return response()->json([
            'employee' => $employee,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => $tokenType,
            'expires_in' => $expiresIn,
            'force_password_change' => $employee->force_password_change,
            'message' => __('messages.login.success'),
        ]);
    }

    /**
     * Logout employee (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user('employee')->token()->revoke();

        return response()->json([
            'message' => __('messages.logout.success'),
        ]);
    }

    /**
     * Refresh access token using refresh token
     */
    public function refresh(RefreshTokenRequest $request)
    {
        // Get the employee from the current token
        $employee = $request->user('employee');

        // Revoke the old token
        $request->user('employee')->token()->revoke();

        // Create token for employee using employee guard
        $tokenResult = $employee->createToken('employee_token');

        // Get data from the token model
        $accessToken = $tokenResult->accessToken;
        $refreshToken = $tokenResult->accessTokenId;
        $tokenType = $tokenResult->tokenType;
        $expiresIn = $tokenResult->expiresIn;

        // Get refresh token from the token model
        $refreshToken = $tokenResult->token->id;

        return response()->json([
            'employee' => $employee,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => $tokenType,
            'expires_in' => $expiresIn,
            'message' => 'Token refreshed successfully',
        ]);
    }

    /**
     * Get authenticated employee information
     */
    public function me(Request $request)
    {
        // The authenticated employee from the employee guard
        $employee = $request->user('employee');

        return response()->json(['data' => $employee]);
    }

    /**
     * Update employee password
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $employee = $request->user('employee');

        $employee->password = Hash::make($request->new_password);
        $employee->force_password_change = false;
        $employee->save();

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }
}
