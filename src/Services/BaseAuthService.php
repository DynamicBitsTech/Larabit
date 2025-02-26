<?php

namespace Dynamicbits\Larabit\Services;

use App\Models\User;
use DateTimeInterface;
use Dynamicbits\Larabit\Helpers\TraitChecker;
use Dynamicbits\Larabit\Notifications\ResetPasswordOTP;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Abstract service class for authentication and password management.
 */
abstract class BaseAuthService
{
    public function __construct(
        private BaseUserService $baseUserService
    ) {
    }

    /**
     * Attempts to authenticate the user with given credentials.
     *
     * @param array $credentials User credentials (e.g., email and password).
     * @param bool $remember Whether to remember the user session.
     * @param array|string|null $roles The required roles for authentication.
     * @param string $authColumn The column used for authentication (default: 'email').
     * @return bool Returns true if authentication succeeds, false otherwise.
     */
    public function login(array $credentials, $remember = false, array|string|null $roles = null, string $authColumn = 'email'): bool
    {
        $authenticated = $this->authenticate($credentials, $remember, $authColumn);
        if (!empty($roles) && !$this->baseUserService->hasRole($roles)) {
            $this->logout();
            return false;
        }
        return $authenticated;
    }

    /**
     * Logs out the currently authenticated user and invalidates the session.
     *
     * @return void
     */
    public function logout(): void
    {
        Auth::logout();
        Request::session()->invalidate();
        Request::session()->regenerate();
    }

    /**
     * Sends a password reset email to the specified email address.
     *
     * @param string $email The email address of the user.
     * @return bool Returns true if the reset link was sent successfully, false otherwise.
     */
    public function passwordEmail(string $email): bool
    {
        return Password::RESET_LINK_SENT === Password::sendResetLink(['email' => $email]);
    }

    /**
     * Updates the user's password using a provided reset token and new password.
     *
     * @param array $credentials The credentials containing email, token, and new password.
     * @return bool Returns true if the password was successfully reset, false otherwise.
     */
    public function passwordUpdate(array $credentials): bool
    {
        $status = Password::reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );
        return $status === Password::PASSWORD_RESET;
    }

    /**
     * Logs in a user via API and generates an authentication token.
     *
     * @param array $credentials User credentials.
     * @param array|string|null $roles The required roles for authentication.
     * @param DateTimeInterface|null $expiresAt Token expiration date.
     * @return string|bool Returns the token string if authentication is successful, false otherwise.
     */
    public function loginApi(array $credentials, array|string|null $roles = null, DateTimeInterface|null $expiresAt = null): string|bool
    {
        $authenticated = $this->login($credentials, false, $roles);
        if ($authenticated) {
            return $this->createToken($expiresAt);
        }
        return false;
    }

    /**
     * Revokes the current API authentication token.
     *
     * @return void
     */
    public function revokeToken(): void
    {
        $this->hasApiTrait();
        $traits = class_uses(User::class);
        $user = request()->user();
        in_array(\Laravel\Sanctum\HasApiTokens::class, $traits)
            ? $user->currentAccessToken()->delete()
            : $user->token()->revoke();
    }

    /**
     * Sends a reset OTP to the given email.
     *
     * @param string $email The email address of the user.
     * @return bool Returns true if the OTP was saved and sent successfully, false otherwise.
     */
    public function sendResetOtp(string $email): bool
    {
        $otp = random_int(100000, 999999);
        $saved = $this->saveResetToken($email, $otp);
        if ($saved) {
            TraitChecker::has(User::class, \Illuminate\Notifications\Notifiable::class);
            $this->baseUserService->query->firstWhere('email', $email)?->notify(new ResetPasswordOTP($otp));
            return $saved;
        }
        return false;
    }

    /**
     * Verifies the reset OTP for the given email.
     *
     * @param string $email The email address of the user.
     * @param string $otp The OTP to verify.
     * @return false|string Returns a signed URL if the OTP is valid, false otherwise.
     */
    public function verifyResetOtp(string $email, string $otp): false|string
    {
        $user = $this->baseUserService->firstByCriteria(['email' => $email]);
        if ($user) {
            $verified = Password::tokenExists($user, $otp);
            if ($verified) {
                return $this->createSignedUrl('api.password.set-new', params: ['email' => $email]);
            }
        }
        return false;
    }

    /**
     * Sets a new password for the user.
     *
     * @param string $email The email address of the user.
     * @param string $password The new password.
     * @return bool Returns true if the password was successfully updated, false otherwise.
     */
    public function passwordSetNew(string $email, string $password): bool
    {
        $user = $this->baseUserService->firstByCriteria(['email' => $email]);
        return $this->baseUserService->update($user, ['password' => Hash::make($password)]);
    }

    /**
     * Authenticates the user with the given credentials.
     *
     * @param array $credentials The user credentials (e.g., email and password).
     * @param bool $remember Whether to remember the user's session.
     * @param string $authColumn The column used for authentication (default: 'email').
     * @return bool Returns true if authentication is successful, false otherwise.
     */
    private function authenticate(array $credentials, bool $remember, $authColumn = 'email'): bool
    {
        if ($authColumn == 'email') {
            return Auth::attempt($credentials, $remember);
        }
        $user = $this->baseUserService->query->where($authColumn, $credentials[$authColumn] ?? ($credentials['email']) ?? '')->first();
        if ($user && Hash::check($credentials['password'] ?? '', $user->password)) {
            Auth::login($user);
            return true;
        }
        return false;
    }

    /**
     * Creates an authentication token for the user.
     *
     * @param DateTimeInterface|null $expiresAt The expiration date of the token.
     * @return string The authentication token.
     */
    private function createToken(DateTimeInterface|null $expiresAt = null): string
    {
        $this->hasApiTrait();
        $token = Auth::user()->createToken('AuthToken', expiresAt: $expiresAt);
        $traits = class_uses(User::class);
        return in_array(\Laravel\Sanctum\HasApiTokens::class, $traits) ? $token->plainTextToken : $token->accessToken;
    }

    /**
     * Checks if the User model has an API token trait.
     */
    private function hasApiTrait(): void
    {
        TraitChecker::hasAny(User::class, [
            \Laravel\Sanctum\HasApiTokens::class,
            \Laravel\Passport\HasApiTokens::class
        ]);
    }

    /**
     * Saves a password reset token for a given email.
     *
     * @param string $email The user's email address.
     * @param string $token The reset token.
     * @return bool Returns true if the token was successfully saved.
     */
    private function saveResetToken(string $email, $token): bool
    {
        return DB::table('password_reset_tokens')
            ->updateOrInsert(
                ['email' => $email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );
    }

    /**
     * Creates a signed URL with an expiration time.
     *
     * @param string $name The name of the route.
     * @param int $expiration The expiration time in minutes.
     * @param array $params Additional parameters for the route.
     * @return string The generated signed URL.
     */
    private function createSignedUrl($name, $expiration = 60, array $params = []): string
    {
        return URL::temporarySignedRoute($name, $expiration, $params);
    }
}
