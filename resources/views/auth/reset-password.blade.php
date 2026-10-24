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
                    <span class="nex-eyebrow">SECURE YOUR ACCOUNT</span>
                    <h2>Choose a new password</h2>
                    <p>Use a strong password you don’t use for other accounts.</p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="nex-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="nex-field">
                        <label for="email">Email address</label>
                        <div class="nex-input-wrapper">
                            <span class="nex-input-icon"><i class="fa-regular fa-envelope"></i></span>
                            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="you@example.com">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="nex-error" />
                    </div>

                    <div class="nex-field">
                        <label for="password">New password</label>
                        <div class="nex-input-wrapper">
                            <span class="nex-input-icon"><i class="fa-solid fa-lock"></i></span>
                            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Create a new password">
                            <button type="button" class="nex-password-toggle" data-password-toggle="password" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="nex-error" />
                    </div>

                    <div class="nex-field">
                        <label for="password_confirmation">Confirm new password</label>
                        <div class="nex-input-wrapper">
                            <span class="nex-input-icon"><i class="fa-solid fa-shield-halved"></i></span>
                            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Enter it again">
                            <button type="button" class="nex-password-toggle" data-password-toggle="password_confirmation" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="nex-error" />
                    </div>

                    <button type="submit" class="nex-submit">
                        <span>Reset password</span><i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="nex-auth-switch nex-recovery-switch">
                    <a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left"></i> Back to sign in</a>
                </div>
            </section>
        </div>
    </main>
</x-guest-layout>
