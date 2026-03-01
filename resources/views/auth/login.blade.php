<!DOCTYPE html>
<html lang="az">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Giriş</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/login.css')
</head>

<body>

    <div class="auth-shell">

        {{-- Left / Branding --}}
        <section class="hero-card">
            <div>
                <div class="brand">
                    <span class="brand-dot"></span>
                    <span>{{ config('app.name', 'MyApp') }}</span>
                </div>

                <div class="hero-content">
                    <h1>Hesabına təhlükəsiz və rahat giriş et</h1>
                    <p>
                        Email/şifrə ilə daxil ol və ya bir kliklə Google / Apple hesabınla giriş et.
                        Sürətli, sadə və modern giriş təcrübəsi.
                    </p>
                </div>

                <div class="features">
                    <div class="feature">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2l7 4v6c0 5-3.5 8-7 10-3.5-2-7-5-7-10V6l7-4z"></path>
                                <path d="M9 12l2 2 4-4"></path>
                            </svg>
                        </div>
                        <div>
                            <h4>Təhlükəsiz Giriş</h4>
                            <p>OAuth və sessiya əsaslı giriş ilə istifadəçi hesablarını təhlükəsiz qoruyun.</p>
                        </div>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4>Bir kliklə login</h4>
                            <p>Google və Apple düymələri ilə istifadəçi onboarding prosesini sürətləndir.</p>
                        </div>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="16" rx="3"></rect>
                                <path d="M7 15h10"></path>
                                <path d="M7 10h6"></path>
                            </svg>
                        </div>
                        <div>
                            <h4>Modern UI</h4>
                            <p>Responsive dizayn ilə desktop və mobil cihazlarda ideal görünüş.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="badge">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
                    <path d="M12 2l2.4 4.9 5.4.8-3.9 3.8.9 5.4-4.8-2.5-4.8 2.5.9-5.4-3.9-3.8 5.4-.8L12 2z" />
                </svg>
                Social Login hazır UI (Google + Apple)
            </div>
        </section>

        {{-- Right / Form --}}
        <section class="login-card">
            <div class="login-header">
                <h2>Xoş gəlmisən 👋</h2>
                <p>Hesabına daxil olmaq üçün məlumatlarını yaz.</p>
            </div>

            {{-- Session messages --}}
            <div class="alerts">
                @if (session('error'))
                    <div class="alert error">{{ session('error') }}</div>
                @endif

                @if (session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif
            </div>

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            placeholder="example@mail.com" required autofocus>
                    </div>
                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Şifrə</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row-between">
                    <label class="remember">
                        <input type="checkbox" name="remember">
                        <span>Məni xatırla</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="link">Şifrəni unutmusan?</a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">
                    Daxil ol
                </button>
            </form>

            <div class="divider">və ya</div>

            {{-- Social Buttons --}}
            <div class="social-grid">
                <a href="{{ route('social.redirect', 'google') }}" class="btn btn-social">
                    {{-- Google icon --}}
                    <svg viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107"
                            d="M43.6 20.5H42V20H24v8h11.3C33.6 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12S17.4 12 24 12c3 0 5.7 1.1 7.8 3l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z" />
                        <path fill="#FF3D00"
                            d="M6.3 14.7l6.6 4.8C14.7 15.2 19 12 24 12c3 0 5.7 1.1 7.8 3l5.7-5.7C34 6.1 29.3 4 24 4c-7.7 0-14.3 4.3-17.7 10.7z" />
                        <path fill="#4CAF50"
                            d="M24 44c5.2 0 10-2 13.6-5.3l-6.3-5.3c-2 1.5-4.5 2.6-7.3 2.6-5.2 0-9.6-3.3-11.2-8l-6.5 5C9.6 39.5 16.2 44 24 44z" />
                        <path fill="#1976D2"
                            d="M43.6 20.5H42V20H24v8h11.3c-.8 2.5-2.4 4.6-4.7 6.1l.1-.1 6.3 5.3C36.6 39 44 34 44 24c0-1.3-.1-2.4-.4-3.5z" />
                    </svg>
                    Google ilə davam et
                </a>

                <a href="{{ route('social.redirect', 'apple') }}" class="btn btn-social">
                    {{-- Apple icon --}}
                    <svg viewBox="0 0 384 512" aria-hidden="true" fill="currentColor">
                        <path
                            d="M318.7 268.4c-.2-36.7 16.4-64.4 49.9-84.8-18.7-26.8-47.1-41.6-84.5-44.5-35.4-2.8-74 20.7-88.1 20.7-14.9 0-49.4-19.7-76.4-19.7C62.9 140.2 0 187.3 0 281.6c0 27.9 5.1 56.8 15.2 86.7 13.5 39.6 62.2 136.7 113 135.1 26.6-.6 45.4-18.9 80-18.9 33.5 0 50.9 18.9 80.6 18.9 51.2-.7 95.2-90.9 108.1-130.6-72.9-34.3-78.2-100.4-78.2-104.4zM259.3 81.3C286.6 48.2 283.8 18 283 7c-24.1 1.4-52 16.4-67.8 35.1-17.4 20.2-27.6 45.2-25.4 73.3 26 2 49-11.3 69.5-34.1z" />
                    </svg>
                    Apple ilə davam et
                </a>
            </div>

            <div class="foot-note">
                Hesabın yoxdur?
                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Qeydiyyatdan keç</a>
                @else
                    <a href="#">Qeydiyyatdan keç</a>
                @endif
            </div>
        </section>
    </div>

</body>

</html>
