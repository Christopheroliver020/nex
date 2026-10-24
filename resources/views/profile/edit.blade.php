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

            {{-- Profile Header --}}
            <section class="nex-profile-card">

                <div class="nex-profile-cover" @if($user->cover_photo) style="background-image: url('{{ asset('storage/' . $user->cover_photo) }}'); background-size: cover; background-position: center" @endif></div>

                <div class="nex-profile-main">

                    {{-- Profile Picture --}}
                    <div class="nex-profile-avatar-wrapper">

                        @if ($user->profile_photo)
                            <img
                                src="{{ asset('storage/' . $user->profile_photo) }}"
                                alt="{{ $user->name }}"
                                class="nex-profile-avatar"
                            >
                        @else
                            <div class="nex-profile-avatar nex-profile-avatar-placeholder">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif

                        <button
                            type="button"
                            class="nex-avatar-edit"
                            onclick="document.getElementById('profile_photo').click()"
                        >
                            <i class="fa-solid fa-camera"></i>
                        </button>

                    </div>

                    {{-- Profile Information --}}
                    <div class="nex-profile-info">

                        <div class="nex-profile-name-row">

                            <div>
                                <h1>{{ $user->name }}</h1>

                                <p class="nex-username">
                                    @<span>{{ $user->username ?? strtolower(str_replace(' ', '', $user->name)) }}</span>
                                </p>
                            </div>

                        </div>

                        <p class="nex-profile-bio">
                            {{ $user->bio ?: 'No bio yet. Tell people something about yourself.' }}
                        </p>

                    </div>

                </div>

                {{-- Statistics --}}
                <div class="nex-profile-stats">

                    <div class="nex-stat">
                        <strong>{{ $postCount }}</strong>
                        <span>Posts</span>
                    </div>

                    <div class="nex-stat">
                        <strong>{{ $followersCount }}</strong>
                        <span>Followers</span>
                    </div>

                    <div class="nex-stat">
                        <strong>{{ $followingCount }}</strong>
                        <span>Following</span>
                    </div>

                </div>

            </section>


            {{-- Edit Profile --}}
            <section class="nex-settings-card">

                <div class="nex-section-header">

                    <div>
                        <span class="nex-section-eyebrow">
                            ACCOUNT SETTINGS
                        </span>

                        <h2>Edit profile</h2>

                        <p>
                            Update your profile information and personalize
                            your NEX account.
                        </p>
                    </div>

                    <div class="nex-section-icon">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>

                </div>


                @if (session('status') === 'profile-updated')
                    <div class="nex-success-message">
                        <i class="fa-solid fa-circle-check"></i>
                        Profile updated successfully.
                    </div>
                @endif


                @if (session('status') === 'profile-photo-updated')
                    <div class="nex-success-message">
                        <i class="fa-solid fa-circle-check"></i>
                        Your photo update was saved as a Friends-only post. Your mutual followers have been notified.
                    </div>
                @endif


                {{-- Profile Form --}}
                <form
                    method="POST"
                    action="{{ route('profile.update') }}"
                    enctype="multipart/form-data"
                    class="nex-profile-form"
                >

                    @csrf
                    @method('PATCH')

                    <div class="nex-form-section">
                        <div class="nex-form-section-title">
                            <i class="fa-regular fa-image"></i>
                            <div><h3>Cover photo</h3><p>Set the banner on your profile. Changes are saved to your Friends-only updates.</p></div>
                        </div>
                        <div class="nex-cover-upload-preview" id="coverPreview" @if($user->cover_photo) style="background-image:url('{{ asset('storage/' . $user->cover_photo) }}')" @endif></div>
                        <label for="cover_photo" class="nex-upload-button"><i class="fa-solid fa-cloud-arrow-up"></i> Choose cover photo</label>
                        <input id="cover_photo" type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp" hidden>
                        <span class="nex-upload-hint">JPG, PNG or WEBP · Max 5MB</span>
                        @error('cover_photo')<p class="nex-error">{{ $message }}</p>@enderror
                    </div>


                    {{-- Profile Photo --}}
                    <div class="nex-form-section">

                        <div class="nex-form-section-title">
                            <i class="fa-solid fa-image"></i>

                            <div>
                                <h3>Profile picture</h3>
                                <p>Choose a photo that represents you. Changes are saved to your Friends-only updates.</p>
                            </div>
                        </div>


                        <div class="nex-photo-upload">

                            <div class="nex-photo-preview">

                                @if ($user->profile_photo)
                                    <img
                                        id="profilePreview"
                                        src="{{ asset('storage/' . $user->profile_photo) }}"
                                        alt="Profile preview"
                                    >
                                @else
                                    <div
                                        id="profilePreviewPlaceholder"
                                        class="nex-photo-placeholder"
                                    >
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif

                            </div>


                            <div class="nex-photo-actions">

                                <label
                                    for="profile_photo"
                                    class="nex-upload-button"
                                >
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    Choose photo
                                </label>

                                <input
                                    id="profile_photo"
                                    type="file"
                                    name="profile_photo"
                                    accept="image/jpeg,image/png,image/webp"
                                    hidden
                                >

                                <span>
                                    JPG, PNG or WEBP · Max 2MB
                                </span>

                            </div>

                        </div>

                        @error('profile_photo')
                            <p class="nex-error">{{ $message }}</p>
                        @enderror

                    </div>


                    <div class="nex-form-actions">
                        <a href="{{ route('profiles.show', $user) }}" class="nex-cancel-button">View profile</a>
                        <button type="submit" class="nex-save-button"><i class="fa-solid fa-check"></i> Save photos</button>
                    </div>
                </form>

            </section>

        </div>

    </div>

</x-app-layout>
