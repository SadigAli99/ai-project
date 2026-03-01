<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    private $providers = ['google', 'apple'];
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, $this->providers), 404);

        if ($provider == 'apple') {
            $response = $this->authService->loginWithApple();
            if ($response['status'] == 'error') return redirect()->back()->with('error', $response['message']);

            return redirect()->to($response['redirect']);
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        abort_unless(in_array($provider, $this->providers), 404);

        if ($provider == 'apple') {
            return redirect()->back()->with('error', 'Apple login hələ aktiv deyil');
        }

        $response = $this->authService->loginWithGoogle();
        if ($response['status'] == 'error') return redirect()->back()->with('error', $response['message']);

        return redirect()->to($response['redirect']);

        return redirect()->to('/');
    }
}
