<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — KTU Helpdesk</title>
    <link rel="icon" href="{{ asset('img/Logo-KTU.jpg') }}" type="image/jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/custom/auth.css') }}">
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">

            {{-- Logo --}}
            <div class="login-logo">
                <div class="login-logo-icon">
                    <img src="{{ asset('img/Logo-KTU.jpg') }}" alt="Logo KTU">
                </div>
                <div>
                    <span class="login-logo-name">KTU Shipyard</span>
                    <span class="login-logo-sub">Sagulung · Batam</span>
                </div>
            </div>

            {{-- Title --}}
            <h2 class="login-title">IT Support Ticketing System</h2>
            <p class="login-subtitle">KTU Shipyard</p>

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
                        placeholder="Enter your username"
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
                               required autocomplete="current-password">
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
                        Remember Me
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-submit">Login</button>
            </form>

            {{-- Footer --}}
            <div class="login-footer">
                &copy; {{ date('Y') }} KTU Shipyard · IT Helpdesk System
            </div>

        </div>
    </div>

    <script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>
