<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — KTU Helpdesk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/custom/auth.css') }}">
</head>
<body>

    {{-- ── HERO SECTION ── --}}
    <div class="hero">
        <div class="hero-bg">
            <div class="hero-bg-placeholder">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
        <div class="hero-overlay"></div>

        <div class="hero-content">
            {{-- Logo --}}
            <div class="hero-logo">
                <div class="hero-logo-icon">
                    <img src="{{ asset('img/Logo-KTU.jpg') }}" alt="Logo KTU" class="logo-image">
                </div>
                <div>
                    <span class="hero-logo-name">KTU Shipyard</span>
                    <span class="hero-logo-sub">IT Support System</span>
                </div>
            </div>

            {{-- Middle --}}
            <div class="hero-middle">
                <div class="hero-image-wrapper">
                    {{-- Uncomment dan ganti src kalau sudah ada gambar asli --}}
                    {{-- <img src="{{ asset('images/hero.jpg') }}" alt="KTU Shipyard"> --}}
                    <div class="hero-image-placeholder">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Company Image Placeholder</span>
                    </div>
                </div>
                <h1 class="hero-title">KTU Shipyard</h1>
                <p class="hero-subtitle">Ticketing Problem IT Support</p>
                <div class="hero-line"></div>
            </div>

            {{-- Tagline --}}
            <div class="hero-tagline">
                Great Ship <span>·</span> Great Value
            </div>
        </div>
    </div>

    {{-- ── LOGIN PANEL ── --}}
    <div class="login-panel">

        {{-- Logo --}}
        <div class="login-panel-logo">
            <div class="login-panel-logo-icon">
                <img src="{{ asset('img/Logo-KTU.jpg') }}" alt="Logo KTU" class="logo-image">
            </div>
            <div>
                <span class="login-panel-logo-name">KTU Shipyard</span>
                <span class="login-panel-logo-sub">Sagulung · Batam</span>
            </div>
        </div>

        {{-- Title --}}
        <h2 class="login-title">Ticketing Problem<br>IT Support</h2>
        <p class="login-subtitle">KTU Shipyard Sagulung</p>

        {{-- Alerts --}}
        @if(session('error'))
            <div class="auth-alert auth-alert-error">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if(session('status'))
            <div class="auth-alert auth-alert-success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Username --}}
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username"
                    class="form-input {{ $errors->has('username') ? 'is-error' : '' }}"
                    value="{{ old('username') }}"
                    placeholder="Masukkan username"
                    required autofocus autocomplete="username">
                @error('username')
                    <span class="form-error-msg">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password"
                           class="form-input {{ $errors->has('password') ? 'is-error' : '' }}"
                           placeholder="••••••••"
                           required autocomplete="current-password"
                           style="padding-right: 40px;">
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <svg id="eyeIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <span class="form-error-msg">{{ $message }}</span>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="form-bottom">
                <label class="remember-label">
                    <input type="checkbox" name="remember" id="remember_me">
                    Ingat saya
                </label>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-submit">Masuk</button>
        </form>

        {{-- Footer --}}
        <div class="login-footer">
            &copy; {{ date('Y') }} KTU Shipyard · IT Helpdesk System
        </div>
    </div>

    <script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>
