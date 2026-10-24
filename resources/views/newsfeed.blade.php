<x-app-layout>

    <div class="nex-app-body">

        <main class="nex-feed-wrapper">

            {{-- =====================================================
                 LEFT SIDEBAR
            ====================================================== --}}

            <aside class="nex-left-sidebar">

                <div class="nex-sidebar-profile">

                    <div class="nex-sidebar-avatar">
                        @if(Auth::user()->profile_photo)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        @endif
                    </div>

                    <div class="nex-sidebar-user">
                        <strong>{{ Auth::user()->name }}</strong>

                        <span>
                            {{ '@' . strtolower(str_replace(' ', '', Auth::user()->name)) }}
                        </span>
                    </div>

                </div>

                <nav class="nex-sidebar-nav">
                    <a href="#">
                        <span>@include('partials.icon', ['icon' => 'settings'])</span>
                        Settings
                    </a>

                    <a href="{{ route('profile.edit') }}">
                        <span>@include('partials.icon', ['icon' => 'user'])</span>
                        Profile
                    </a>

                </nav>

                <div class="nex-sidebar-card">

                    <span class="nex-sidebar-card-icon">@include('partials.icon', ['icon' => 'sparkles'])</span>

                    <strong>Build your NEX</strong>

                    <p>
                        Share your thoughts and connect with the community.
                    </p>

                </div>

            </aside>


            {{-- =====================================================
                CENTER FEED
            ====================================================== --}}

            <section class="nex-feed">

                {{-- SUCCESS MESSAGE --}}
                @if(session('success'))

                    <div class="nex-success-message">
                        {{ session('success') }}
                    </div>

                @endif

                @include('partials.myday-stories')


                {{-- =================================================
                    CREATE POST
                ================================================== --}}

                <div id="create-post" class="nex-create-card">

                    <div class="nex-create-top">

                        <a href="{{ route('profiles.show', Auth::user()) }}" class="nex-avatar nex-avatar-link" aria-label="View your profile">
                            @if(Auth::user()->profile_photo)<img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="">@else{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}@endif
                        </a>

                        <div>
                            <strong>
                                {{ Auth::user()->name }}
                            </strong>

                            <span>
                                Share something with NEX
                            </span>
                        </div>

                    </div>


                    <form
                        method="POST"
                        action="{{ route('newsfeed.store') }}"
                        enctype="multipart/form-data"
                        class="nex-post-form"
                    >

                        @csrf

                        <textarea
                            name="content"
                            placeholder="What's on your mind?"
                        >{{ old('content') }}</textarea>

                        <div class="nex-post-media-preview" data-post-media-preview hidden>
                            <div class="nex-post-media-preview-stage" data-post-media-stage></div>
                            <div class="nex-post-media-preview-footer"><span data-post-media-name></span><button type="button" data-post-media-remove>Remove</button></div>
                        </div>

                        <label class="nex-audience-field" for="post-audience">
                            <span>Who can see this?</span>
                            <select id="post-audience" name="audience" required>
                                <option value="public" @selected(old('audience', 'public') === 'public')>Public</option>
                                <option value="friends" @selected(old('audience') === 'friends')>Friends only</option>
                            </select>
                        </label>


                        @error('content')

                            <div class="nex-form-error">
                                {{ $message }}
                            </div>

                        @enderror


                        <div class="nex-create-actions">

                            <div class="nex-media-buttons">

                                <label>
                                    <span>@include('partials.icon', ['icon' => 'image'])</span>
                                    <span class="nex-media-text">Photo</span>

                                    <input
                                        type="file"
                                        name="media"
                                        accept="image/*"
                                        data-post-media-input
                                        hidden
                                    >
                                </label>


                                <label>
                                    <span>@include('partials.icon', ['icon' => 'video'])</span>
                                    <span class="nex-media-text">Video</span>

                                    <input
                                        type="file"
                                        name="media"
                                        accept="video/*"
                                        data-post-media-input
                                        hidden
                                    >
                                </label>

                                <small class="nex-media-limit">Photos and videos up to 35 MB</small>

                            </div>


                            <button
                                type="submit"
                                class="nex-post-button"
                            >
                                Post
                            </button>

                        </div>

                        <div class="nex-post-upload-progress" data-post-progress hidden aria-live="polite">
                            <span class="nex-post-progress-ring" data-post-progress-ring style="--progress:0%"><b data-post-progress-percent>0%</b></span>
                            <span class="nex-post-progress-copy"><strong data-post-progress-title>Publishing your post…</strong><small data-post-progress-detail>Your text and media are being sent to NEX.</small></span>
                            <div class="nex-post-progress-track" role="progressbar" aria-label="Post upload progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span data-post-progress-fill></span></div>
                        </div>
                        <div class="nex-post-upload-error" data-post-upload-error role="alert" hidden>
                            <span data-post-error-message></span>
                            <button type="button" data-post-retry hidden>Retry upload</button>
                        </div>

                    </form>

                </div>

                {{-- =================================================
                    FEED FILTERS
                ================================================== --}}

                <div class="nex-feed-tabs">

                    <button type="button" class="active">
                        For you
                    </button>

                    <button type="button">
                        Following
                    </button>

                    <button type="button">
                        Latest
                    </button>

                </div>


                {{-- =================================================
                    POSTS
                ================================================== --}}

                @include('partials.posts-list')

            </section>


            {{-- =====================================================
                 RIGHT SIDEBAR
            ====================================================== --}}

            <aside class="nex-right-sidebar">

                <div class="nex-widget">

                    <div class="nex-widget-header">

                        <h3>
                            People to follow
                        </h3>

                        <a href="#">
                            See all
                        </a>

                    </div>


                    @foreach($peopleToFollow as $person)

                        <div class="nex-person">

                            <div class="nex-person-avatar">
                                @if($person->profile_photo)
                                    <img src="{{ asset('storage/' . $person->profile_photo) }}" alt="{{ $person->name }}">
                                @else
                                    {{ strtoupper(substr($person->name, 0, 1)) }}
                                @endif
                            </div>

                            <div class="nex-person-info">

                                <strong>
                                    {{ $person->name }}
                                </strong>

                                <span>
                                    {{ '@' . strtolower(str_replace(' ', '', $person->name)) }}
                                </span>

                            </div>

                            <form method="POST" action="{{ route('users.follow', $person) }}">
                                @csrf
                                <button type="submit">Follow</button>
                            </form>

                        </div>

                    @endforeach

                </div>


                <div class="nex-widget">

                    <div class="nex-widget-header">

                        <h3>
                            Trending on NEX
                        </h3>

                    </div>

                    <a href="#" class="nex-trending">

                        {{-- <span>01</span> --}}

                        <div>
                            <strong>#Laravel</strong>
                            <small>1.8K posts</small>
                        </div>

                    </a>


                    <a href="#" class="nex-trending">

                        {{-- <span>02</span> --}}

                        <div>
                            <strong>#Technology</strong>
                            <small>923 posts</small>
                        </div>

                    </a>


                    <a href="#" class="nex-trending">

                        {{-- <span>03</span> --}}

                        <div>
                            <strong>#NEX</strong>
                            <small>641 posts</small>
                        </div>

                    </a>

                </div>

            </aside>

        </main>

    </div>

</x-app-layout>
