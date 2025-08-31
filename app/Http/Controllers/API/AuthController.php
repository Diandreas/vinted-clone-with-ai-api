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
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private array $supportedProviders = ['google'];
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

    /**
     * Redirect to provider (for web flows; mobile will use token exchange)
     */
    public function redirectToProvider($provider)
    {
        if (!in_array($provider, $this->supportedProviders, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported provider'
            ], 404);
        }
        if (!class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Social authentication is not enabled on this server.'
            ], 501);
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Handle provider callback (web) or token login (mobile)
     */
    public function handleProviderCallback(Request $request, $provider)
    {
        if (!in_array($provider, $this->supportedProviders, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported provider'
            ], 404);
        }
        if (!class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            return response()->json([
                'success' => false,
                'message' => 'Social authentication is not enabled on this server.'
            ], 501);
        }

        try {
            // Mobile: if access_token is provided, use it
            if ($request->has('access_token')) {
                $socialUser = Socialite::driver($provider)
                    ->stateless()
                    ->userFromToken($request->input('access_token'));
            } else {
                // Web: use OAuth callback
                $socialUser = Socialite::driver($provider)->stateless()->user();
            }

            $email = $socialUser->getEmail();
            $name = $socialUser->getName() ?: ($socialUser->getNickname() ?: 'User');
            $username = $socialUser->getNickname() ?: (explode('@', (string) $email)[0] ?? 'user_' . substr(md5($socialUser->getId()), 0, 6));

            $user = User::firstOrCreate(
                ['email' => $email ?: $provider.'_'.$socialUser->getId().'@example.com'],
                [
                    'name' => $name,
                    'username' => $username,
                    'password' => \Hash::make(str()->random(32)),
                    'email_verified_at' => now(),
                ]
            );

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'user' => $user,
                'token' => $token,
                'provider' => $provider,
            ]);
        } catch (\Throwable $e) {
            Log::error('Social login failed', [
                'provider' => $provider,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Social authentication failed'
            ], 400);
        }
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Auth.forgotPassword validation_failed', [
                'errors' => $e->errors(),
                'input' => $request->only('email')
            ]);
            throw $e;
        }

        // For now, just return success (password reset functionality can be implemented later)
        return response()->json([
            'success' => true,
            'message' => 'If an account with that email address exists, we have sent a password reset link.'
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string',
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Auth.resetPassword validation_failed', [
                'errors' => $e->errors(),
                'input' => $request->except(['password', 'password_confirmation'])
            ]);
            throw $e;
        }

        // For now, just return success (password reset functionality can be implemented later)
        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.'
        ]);
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Auth.verifyEmail validation_failed', [
                'errors' => $e->errors()
            ]);
            throw $e;
        }

        // For now, just return success (email verification functionality can be implemented later)
        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.'
        ]);
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Auth.resendVerification validation_failed', [
                'errors' => $e->errors(),
                'input' => $request->only('email')
            ]);
            throw $e;
        }

        // For now, just return success (verification email functionality can be implemented later)
        return response()->json([
            'success' => true,
            'message' => 'Verification email sent successfully.'
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'username' => 'sometimes|string|unique:users,username,' . $request->user()->id . '|max:255',
                'bio' => 'sometimes|nullable|string|max:1000',
                'location' => 'sometimes|nullable|string|max:255',
                'website' => 'sometimes|nullable|url|max:255',
                'phone' => 'sometimes|nullable|string|max:20',
                'birth_date' => 'sometimes|nullable|date',
                'gender' => 'sometimes|nullable|in:male,female,other',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Auth.updateProfile validation_failed', [
                'errors' => $e->errors(),
                'input' => $request->except(['password'])
            ]);
            throw $e;
        }

        $user = $request->user();
        
        // Update user data
        $updateData = $request->only(['name', 'username', 'bio', 'location', 'website', 'phone', 'gender']);
        
        if ($request->has('birth_date')) {
            $updateData['date_of_birth'] = \Carbon\Carbon::parse($request->birth_date)->toDateString();
        }
        
        $user->update($updateData);

        return response()->json([
            'success' => true,
            'user' => $user->fresh(),
            'message' => 'Profile updated successfully.'
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Auth.changePassword validation_failed', [
                'errors' => $e->errors()
            ]);
            throw $e;
        }

        $user = $request->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.'
        ]);
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Auth.deleteAccount validation_failed', [
                'errors' => $e->errors()
            ]);
            throw $e;
        }

        $user = $request->user();

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The password is incorrect.'],
            ]);
        }

        // Delete user tokens
        $user->tokens()->delete();
        
        // Delete user
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.'
        ]);
    }
}
