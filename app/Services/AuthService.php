<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthService
{

    public function login(array $data): array
    {
        $credentials = ['email' => $data['email'], 'password' => $data['password']];
        $rememberMe = isset($data['remember_me']) ? true : false;
        $authAttempt = Auth::attempt($credentials, $rememberMe);
        if (!$authAttempt) return ['status' => 'error', 'message' => 'Email və ya parol yanlışdır.'];
        return ['status' => 'success', 'message' => 'Hesaba uğurla daxil oldunuz'];
    }

    public function loginWithGoogle(): array
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Get Google User Detail
            $email = $googleUser->getEmail();
            $username = $googleUser->getName() ?: $googleUser->getNickname() ?: 'User';

            if (!$email)
                return ['error' => 'Bu email ilə Google hesabı tapılmadı'];

            // Create User if is not exists

            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'email' => $email,
                    'name' => $username,
                    'password' => bcrypt(Str::random(16)),
                    'email_verified_at' => now(),
                ]);
            } else {
                if (empty($user->name) && $username) {
                    $user->name == $username;
                }

                if (empty($user->email_verified_at)) {
                    $user->email_verified_at = now();
                }
                $user->save();
            }

            Auth::login($user);

            return ['status' => 'success', 'message' => 'Hesaba uğurla daxil oldunuz', 'redirect' => '/'];
        } catch (\Exception $ex) {

            Log::error(json_encode([$ex->getFile(), $ex->getLine(), $ex->getMessage()]));
            return ['status' => 'error', 'message' => 'Xəta baş verdi, zəhmət olmasa, loglara baxın.'];
        }
    }

    public function loginWithApple(): array
    {
        try {
            return ['status' => 'error', 'message' => 'Apple ilə giriş hələ ki aktiv deyil'];

            return ['status' => 'success', 'message' => 'Hesaba uğurla daxil oldunuz'];
        } catch (\Exception $ex) {
            Log::error(json_encode([$ex->getFile(), $ex->getLine(), $ex->getMessage()]));
            return ['status' => 'error', 'message' => 'Xəta baş verdi, zəhmət olmasa, loglara baxın.'];
        }
    }
}
