<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Log incoming request (sanitized)
        Log::info('Auth.register request', [
            'input' => $request->except(['password', 'password_confirmation'])
        ]);

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|unique:users|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string',
                // Front sends birth_date; DB column is date_of_birth
                'birth_date' => 'nullable|date',
                'gender' => 'nullable|in:male,female,other',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Auth.register validation_failed', [
                'errors' => $e->errors(),
                'input' => $request->except(['password', 'password_confirmation'])
            ]);
            throw $e;
        }

        $birthDate = $request->birth_date
            ? Carbon::parse($request->birth_date)->toDateString()
            : null;

        Log::debug('Auth.register parsed_birth_date', [
            'birth_date_raw' => $request->birth_date,
            'date_of_birth' => $birthDate
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                // Map to correct DB column
                'date_of_birth' => $birthDate,
                'gender' => $request->gender,
            ]);
        } catch (\Throwable $e) {
            Log::error('Auth.register exception_on_create', [
                'message' => $e->getMessage(),
                'trace' => collect(explode("\n", $e->getTraceAsString()))->take(5),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again later.'
            ], 500);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('Auth.register success', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Auth.login validation_failed', [
                'errors' => $e->errors(),
                'input' => $request->only('email')
            ]);
            throw $e;
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::info('Auth.login invalid_credentials', ['email' => $request->email]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        try {
            $token = $user->createToken('auth_token')->plainTextToken;
            // Update supported column name
            $user->update(['last_seen_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('Auth.login exception_on_token', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again later.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        $request->user()->update(['last_seen_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()->load(['followers', 'following'])
        ]);
    }
}
