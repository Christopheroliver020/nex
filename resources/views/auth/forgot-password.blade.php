<x-guest-layout>
    @vite(['resources/css/nex-auth.css', 'resources/js/nex-auth.js'])

    <main class="nex-auth-page nex-auth-recovery-page">
        <div class="nex-bg-glow nex-bg-glow-one"></div>
        <div class="nex-bg-glow nex-bg-glow-two"></div>

        <div class="nex-auth-container nex-auth-single">
            <section class="nex-auth-card">
                <a href="{{ url('/') }}" class="nex-logo nex-recovery-logo">
                    <span class="nex-logo-icon">N</span><span>NEX</span>
                </a>

                <div class="nex-auth-header nex-recovery-header">
                    <span class="nex-eyebrow">ACCOUNT RECOVERY</span>
                    <h2>Forgot your password?</h2>
                    <p>Enter the email address on your account and we’ll send instructions to reset your password.</p>
                </div>

                <x-auth-session-status class="nex-auth-message nex-success nex-recovery-status" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="nex-form">
                    @csrf
                    <div class="nex-field">
                        <label for="email">Email address</label>
                        <div class="nex-input-wrapper">
                            <span class="nex-input-icon"><i class="fa-regular fa-envelope"></i></span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@example.com">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="nex-error" />
                    </div>

                    <button type="submit" class="nex-submit">
                        <span>Email reset instructions</span><i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="nex-auth-switch nex-recovery-switch">
                    <a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left"></i> Back to sign in</a>
                </div>
            </section>
        </div>
    </main>
</x-guest-layout>
