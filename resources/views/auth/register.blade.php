<x-guest-layout>
    @vite(['resources/css/nex-auth.css', 'resources/js/nex-auth.js'])

    <div class="nex-auth-page">

        {{-- Background --}}
        <div class="nex-bg-glow nex-bg-glow-one"></div>
        <div class="nex-bg-glow nex-bg-glow-two"></div>

        <div class="nex-auth-container nex-register-container">

            {{-- Left Branding --}}
            <div class="nex-auth-brand">
                <a href="{{ url('/') }}" class="nex-logo">
                    <span class="nex-logo-icon">N</span>
                    <span>NEX</span>
                </a>

                <div class="nex-brand-content">
                    <span class="nex-eyebrow">JOIN NEX</span>

                    <h1>
                        Your world.
                        <span>Your people.</span>
                        Your NEX.
                    </h1>

                    <p>
                        Create your account and start connecting,
                        sharing, and discovering with the NEX community.
                    </p>
                </div>

                <div class="nex-feature-list">

                    <div class="nex-feature">
                        <span class="nex-feature-icon">
                            <i class="fa-solid fa-user-group"></i>
                        </span>

                        <div>
                            <strong>Connect</strong>
                            <span>Find people and build connections.</span>
                        </div>
                    </div>

                    <div class="nex-feature">
                        <span class="nex-feature-icon">
                            <i class="fa-solid fa-image"></i>
                        </span>

                        <div>
                            <strong>Share</strong>
                            <span>Post your thoughts and moments.</span>
                        </div>
                    </div>

                    <div class="nex-feature">
                        <span class="nex-feature-icon">
                            <i class="fa-solid fa-compass"></i>
                        </span>

                        <div>
                            <strong>Discover</strong>
                            <span>Explore what's happening around you.</span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Register Card --}}
            <div class="nex-auth-card nex-register-card">

                <div class="nex-mobile-logo">
                    <a href="{{ url('/') }}" class="nex-logo">
                        <span class="nex-logo-icon">N</span>
                        <span>NEX</span>
                    </a>
                </div>

                <div class="nex-auth-header">
                    <h2>Create your account</h2>
                    <p>Join NEX and start building your community.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="nex-form">
                    @csrf

                    {{-- Name --}}
                    <div class="nex-field">
                        <label for="name">Name</label>

                        <div class="nex-input-wrapper">
                            <span class="nex-input-icon">
                                <i class="fa-regular fa-user"></i>
                            </span>

                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Your name"
                            >
                        </div>

                        <x-input-error
                            :messages="$errors->get('name')"
                            class="nex-error"
                        />
                    </div>

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
                        <label for="password">Password</label>

                        <div class="nex-input-wrapper">
                            <span class="nex-input-icon">
                                <i class="fa-solid fa-lock"></i>
                            </span>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Create a password"
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

                    {{-- Confirm Password --}}
                    <div class="nex-field">
                        <label for="password_confirmation">
                            Confirm password
                        </label>

                        <div class="nex-input-wrapper">
                            <span class="nex-input-icon">
                                <i class="fa-solid fa-shield-halved"></i>
                            </span>

                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm your password"
                            >

                            <button
                                type="button"
                                class="nex-password-toggle"
                                data-password-toggle="password_confirmation"
                                aria-label="Show password"
                            >
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>

                        <x-input-error
                            :messages="$errors->get('password_confirmation')"
                            class="nex-error"
                        />
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="nex-submit">
                        <span>Create NEX account</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                {{-- Login --}}
                <div class="nex-auth-switch">
                    <span>Already have an account?</span>

                    <a href="{{ route('login') }}">
                        Sign in
                    </a>
                </div>

                <div class="nex-divider">
                    <span>WELCOME TO NEX</span>
                </div>

                <p class="nex-terms">
                    By creating an account, you agree to use NEX responsibly
                    and respect other members of the community.
                </p>

            </div>
        </div>
    </div>
</x-guest-layout>
