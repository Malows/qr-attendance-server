<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdatePasswordRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign default supervisor role to new users
        // Specify the 'api' guard explicitly
        $supervisorRole = \Spatie\Permission\Models\Role::findByName('supervisor', 'api');
        $user->assignRole($supervisorRole);

        $tokenResult = $user->createToken('auth_token');

        // Get data from the token model
        $accessToken = $tokenResult->accessToken;
        $refreshToken = $tokenResult->accessTokenId;
        $tokenType = $tokenResult->tokenType;
        $expiresIn = $tokenResult->expiresIn;

        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => $tokenType,
            'expires_in' => $expiresIn,
        ], 201);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->username)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        // If user has force_password_change flag, allow login without password
        if (! $user->force_password_change) {
            // Normal authentication - password is required
            if (! $request->password || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'username' => ['The provided credentials are incorrect.'],
                ]);
            }
        }

        $tokenResult = $user->createToken('auth_token');

        // Get data from the token model
        $accessToken = $tokenResult->accessToken;
        $refreshToken = $tokenResult->accessTokenId;
        $tokenType = $tokenResult->tokenType;
        $expiresIn = $tokenResult->expiresIn;

        // Get refresh token from the token model
        $refreshToken = $tokenResult->token->id;

        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => $tokenType,
            'expires_in' => $expiresIn,
            'force_password_change' => $user->force_password_change,
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Refresh access token using refresh token
     */
    public function refresh(RefreshTokenRequest $request)
    {
        // Get the user from the current token
        $user = $request->user();

        // Revoke the old token
        $request->user()->token()->revoke();

        // Create a new token
        $tokenResult = $user->createToken('auth_token');

        // Get data from the token model
        $accessToken = $tokenResult->accessToken;
        $refreshToken = $tokenResult->accessTokenId;
        $tokenType = $tokenResult->tokenType;
        $expiresIn = $tokenResult->expiresIn;

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => $tokenType,
            'expires_in' => $expiresIn,
            'message' => 'Token refreshed successfully',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Update user password
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user('api');

        $user->password = Hash::make($request->new_password);
        $user->force_password_change = false;
        $user->save();

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }
}
