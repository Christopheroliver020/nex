<x-guest-layout>
    @vite(['resources/css/nex-auth.css', 'resources/js/nex-auth.js'])

    <div class="nex-auth-page">

        {{-- Background --}}
        <div class="nex-bg-glow nex-bg-glow-one"></div>
        <div class="nex-bg-glow nex-bg-glow-two"></div>

        <div class="nex-auth-container">

            {{-- Left Branding --}}
            <div class="nex-auth-brand">
                <a href="{{ url('/') }}" class="nex-logo">
                    <span class="nex-logo-icon">N</span>
                    <span>NEX</span>
                </a>

                <div class="nex-brand-content">
                    <span class="nex-eyebrow">WELCOME BACK</span>

                    <h1>
                        Connect.
                        <span>Share.</span>
                        Discover.
                    </h1>

                    <p>
                        Welcome back to NEX. Connect with people,
                        share your moments, and discover something new.
                    </p>
                </div>

                <div class="nex-brand-footer">
                    <span class="nex-status-dot"></span>
                    <span>NEX community is waiting for you</span>
                </div>
            </div>

            {{-- Login Card --}}
            <div class="nex-auth-card">

                <div class="nex-mobile-logo">
                    <a href="{{ url('/') }}" class="nex-logo">
                        <span class="nex-logo-icon">N</span>
                        <span>NEX</span>
                    </a>
                </div>

                <div class="nex-auth-header">
                    <h2>Welcome back</h2>
                    <p>Sign in to continue to your NEX account.</p>
                </div>

                {{-- Session Status --}}
                <x-auth-session-status
                    class="nex-auth-message nex-success"
                    :status="session('status')"
                />

                <form method="POST" action="{{ route('login') }}" class="nex-form">
                    @csrf

                    {{-- Email --}}
                    <div class="nex-field">
                        <label for="email">Email address</label>

                        <div class="nex-input-wrapper">
                            <span class="nex-input-icon">
                                <i class="fa-regular fa-envelope"></i>
                            </span>

                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="you@example.com"
                            >
                        </div>

                        <x-input-error
                            :messages="$errors->get('email')"
                            class="nex-error"
                        />
                    </div>

                    {{-- Password --}}
                    <div class="nex-field">
                        <div class="nex-label-row">
                            <label for="password">Password</label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <div class="nex-input-wrapper">
                            <span class="nex-input-icon">
                                <i class="fa-solid fa-lock"></i>
                            </span>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                            >

                            <button
                                type="button"
                                class="nex-password-toggle"
                                data-password-toggle="password"
                                aria-label="Show password"
                            >
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>

                        <x-input-error
                            :messages="$errors->get('password')"
                            class="nex-error"
                        />
                    </div>

                    {{-- Remember --}}
                    <label class="nex-checkbox">
                        <input
                            id="remember_me"
                            type="checkbox"
                            name="remember"
                        >

                        <span class="nex-checkmark"></span>

                        <span>Remember me</span>
                    </label>

                    {{-- Submit --}}
                    <button type="submit" class="nex-submit">
                        <span>Sign in</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                {{-- Register --}}
                <div class="nex-auth-switch">
                    <span>Don't have an account?</span>

                    <a href="{{ route('register') }}">
                        Create account
                    </a>
                </div>

                <div class="nex-divider">
                    <span>SECURE ACCESS</span>
                </div>

                <p class="nex-terms">
                    By signing in, you agree to use NEX responsibly
                    and follow our community guidelines.
                </p>

            </div>
        </div>
    </div>
</x-guest-layout>
