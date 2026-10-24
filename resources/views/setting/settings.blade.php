<x-app-layout>

    @vite([
        'resources/css/nex-profile.css',
        'resources/js/nex-profile.js'
    ])

    <div class="nex-profile-page">

        {{-- Background --}}
        <div class="nex-profile-glow nex-profile-glow-one"></div>
        <div class="nex-profile-glow nex-profile-glow-two"></div>

        <div class="nex-profile-container">

            <section class="nex-settings-card">
                <div class="nex-section-header">
                    <div>
                        <span class="nex-section-eyebrow">ACCOUNT PROFILE</span>
                        <h2>Personal information</h2>
                        <p>Update your name, email, username, and profile details.</p>
                    </div>
                    <div class="nex-section-icon"><i class="fa-solid fa-user-pen"></i></div>
                </div>

                @if (session('status') === 'account-details-updated')
                    <div class="nex-success-message"><i class="fa-solid fa-circle-check"></i> Account details saved.</div>
                @endif

                <form method="POST" action="{{ route('settings.profile.update') }}" class="nex-profile-form">
                    @csrf
                    @method('PATCH')
                    <div class="nex-form-grid">
                        <div class="nex-form-group">
                            <label for="name">Full name</label>
                            <div class="nex-input-wrapper"><i class="fa-regular fa-user"></i><input id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"></div>
                            @error('name')<p class="nex-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="nex-form-group">
                            <label for="email">Email address</label>
                            <div class="nex-input-wrapper"><i class="fa-regular fa-envelope"></i><input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email"></div>
                            @error('email')<p class="nex-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="nex-form-group">
                        <label for="username">Username <span aria-hidden="true">*</span></label>
                        <div class="nex-input-wrapper"><span class="nex-input-prefix">@</span><input id="username" name="username" value="{{ old('username', $user->username) }}" required minlength="3" maxlength="30" pattern="[A-Za-z0-9_-]+" autocomplete="username" placeholder="Choose a username"></div>
                        @error('username')<p class="nex-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="nex-form-group">
                        <div class="nex-label-row"><label for="bio">Bio</label><span id="bioCounter">{{ strlen($user->bio ?? '') }}/160</span></div>
                        <textarea id="bio" name="bio" rows="3" maxlength="160">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')<p class="nex-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="nex-form-group">
                        <label for="status">Status</label>
                        <div class="nex-input-wrapper"><i class="fa-regular fa-face-smile"></i><input id="status" name="status" value="{{ old('status', $user->status) }}" maxlength="100"></div>
                        @error('status')<p class="nex-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="nex-form-actions"><button type="submit" class="nex-save-button"><i class="fa-solid fa-check"></i> Save account details</button></div>
                </form>
            </section>

            {{-- Password --}}
            <section class="nex-settings-card">

                <div class="nex-section-header">

                    <div>
                        <span class="nex-section-eyebrow">
                            SECURITY
                        </span>

                        <h2>Change password</h2>

                        <p>
                            Keep your NEX account secure with a strong password.
                        </p>
                    </div>

                    <div class="nex-section-icon">
                        <i class="fa-solid fa-lock"></i>
                    </div>

                </div>

                <div class="nex-breeze-form">
                    @include('profile.partials.update-password-form')
                </div>

            </section>


            {{-- Delete Account --}}
            <section class="nex-settings-card nex-danger-card">

                <div class="nex-section-header">

                    <div>
                        <span class="nex-section-eyebrow nex-danger-text">
                            DANGER ZONE
                        </span>

                        <h2>Delete account</h2>

                        <p>
                            Permanently delete your NEX account and all
                            associated information.
                        </p>
                    </div>

                    <div class="nex-section-icon nex-danger-icon">
                        <i class="fa-solid fa-trash"></i>
                    </div>

                </div>

                <div class="nex-breeze-form">
                    @include('profile.partials.delete-user-form')
                </div>

            </section>

        </div>

    </div>

</x-app-layout>
