<x-app-layout>
    @vite(['resources/css/nex-profile.css', 'resources/css/nex-feed.css'])

    <main class="nex-profile-page nex-public-profile-page">
        <div class="nex-profile-glow nex-profile-glow-one"></div>
        <div class="nex-profile-glow nex-profile-glow-two"></div>
        <div class="nex-profile-container">
            @if(session('success'))<div class="nex-profile-feedback">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="nex-profile-feedback nex-profile-feedback-error">{{ $errors->first() }}</div>@endif
            <section class="nex-profile-card nex-public-profile" id="profile-top">
                <div class="nex-profile-cover" @if($user->cover_photo) style="background-image:url('{{ asset('storage/' . $user->cover_photo) }}');background-size:cover;background-position:center" @endif></div>
                <div class="nex-profile-main">
                    <div class="nex-profile-avatar-wrapper">
                        @if($user->profile_photo)
                            <img class="nex-profile-avatar" src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}">
                        @else
                            <div class="nex-profile-avatar nex-profile-avatar-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                        @endif
                    </div>
                    <div class="nex-profile-info">
                        <h1>{{ $user->name }}</h1>
                        <p class="nex-profile-bio">{{ $user->bio ?: 'This user has not added a bio yet.' }}</p>
                    </div>
                    @if(auth()->id() === $user->id)
                        <a class="nex-profile-edit-link" href="{{ route('profile.edit') }}"><i class="fa-solid fa-pen"></i> Edit profile</a>
                    @else
                        <div class="nex-profile-actions">
                            @if($isFollowing)
                                <form class="nex-profile-follow-form" method="POST" action="{{ route('users.unfollow', $user) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="nex-profile-following-button" type="submit">Following</button>
                                </form>
                            @else
                                <form class="nex-profile-follow-form" method="POST" action="{{ route('users.follow', $user) }}">
                                    @csrf
                                    <button class="nex-profile-follow-button" type="submit"><i class="fa-solid fa-user-plus"></i> Follow</button>
                                </form>
                            @endif
                            <a class="nex-profile-message-link" href="{{ route('messages.show', $user) }}">Message</a>
                            @if(!$user->is_admin)
                                <details class="nex-profile-report">
                                    <summary aria-label="Report account">@include('partials.icon', ['icon' => 'flag'])<span>Report</span></summary>
                                    <form method="POST" action="{{ route('users.report', $user) }}">
                                        @csrf
                                        <strong>Report {{ $user->name }}’s account</strong>
                                        <label>Reason
                                            <select name="reason" required>
                                                <option value="">Choose a reason</option>
                                                <option value="spam">Spam</option>
                                                <option value="harassment">Harassment</option>
                                                <option value="inappropriate">Inappropriate content</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </label>
                                        <textarea name="details" maxlength="1000" placeholder="Tell NEX what happened (optional)"></textarea>
                                        <button type="submit">Submit report</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="nex-profile-stats">
                    <div class="nex-stat"><strong>{{ $user->posts_count }}</strong><span>Posts</span></div>
                    <div class="nex-stat"><strong>{{ $user->followers_count }}</strong><span>Followers</span></div>
                    <div class="nex-stat"><strong>{{ $user->following_count }}</strong><span>Following</span></div>
                </div>
            </section>

            @if($user->myDayHighlights->isNotEmpty())
                <section class="nex-profile-highlights" aria-label="Featured stories">
                    <div class="nex-profile-posts-heading"><span class="nex-section-eyebrow">FEATURED</span><h2>{{ auth()->id() === $user->id ? 'Your highlights' : $user->name . '’s highlights' }}</h2></div>
                    <div class="nex-profile-highlight-grid">
                        @foreach($user->myDayHighlights as $highlight)
                            @php($featuredStory = $highlight->stories->sortByDesc('created_at')->first())
                            <article class="nex-profile-highlight">
                                <a href="{{ route('myday.show', $featuredStory) }}">
                                    @if($featuredStory->media_type === 'image')<img src="{{ route('myday.media', [$featuredStory, 'media']) }}" alt="">@else<div class="nex-profile-highlight-video">▶</div>@endif
                                    <strong>{{ $highlight->title }}</strong>
                                    @if($highlight->description)<span>{{ $highlight->description }}</span>@endif
                                </a>
                                @if(auth()->id() === $user->id)<form method="POST" action="{{ route('myday.highlights.destroy', $highlight) }}">@csrf @method('DELETE')<button type="submit">Remove</button></form>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

                <div class="nex-profile-overview-grid">
                <section class="nex-profile-about-card" id="profile-about">
                    <header><div><span class="nex-section-eyebrow">GET TO KNOW</span><h2>About {{ $user->name }}</h2></div><span class="nex-profile-about-jp" aria-hidden="true">プロフィール</span></header>
                    <p class="nex-profile-about-bio">{{ $user->bio ?: 'This user has not added a bio yet.' }}</p>
                    <div class="nex-profile-about-details">
                        <div><span class="nex-profile-about-detail-icon">@include('partials.icon', ['icon' => 'user'])</span><span><small>Username</small><strong>{{ '@' . ($user->username ?? strtolower(str_replace(' ', '', $user->name))) }}</strong></span></div>
                        @if($user->status)<div><span class="nex-profile-about-detail-icon">@include('partials.icon', ['icon' => 'sparkles'])</span><span><small>Status</small><strong>{{ $user->status }}</strong></span></div>@endif
                        <div><span class="nex-profile-about-detail-icon">@include('partials.icon', ['icon' => 'messages'])</span><span><small>Community</small><strong>NEX member</strong></span></div>
                    </div>
                </section>

                <section class="nex-profile-photos" aria-label="Profile media">
                    <div class="nex-profile-posts-heading" id="profile-media"><span class="nex-section-eyebrow">YOUR COLLECTIONS</span><h2>{{ $user->name }}’s media</h2><p>Browse profile pictures, photos, and videos.</p></div>
                    <div class="nex-profile-media-cards">
                        @foreach([
                            'profile-pictures' => ['title' => 'Profile pictures', 'icon' => 'user'],
                            'photos' => ['title' => 'Photos', 'icon' => 'image'],
                            'videos' => ['title' => 'Videos', 'icon' => 'video'],
                        ] as $type => $section)
                            @php($items = $profileMedia[$type])
                            <a class="nex-profile-media-card" href="{{ route('profiles.media', [$user, $type]) }}">
                                <span class="nex-profile-media-card-preview">
                                    @if($items->isNotEmpty() && $items->first()['media_type'] === 'image')
                                        <img src="{{ asset('storage/' . $items->first()['path']) }}" alt="" loading="lazy">
                                    @elseif($items->isNotEmpty())
                                        <span class="nex-profile-media-card-video">@include('partials.icon', ['icon' => 'play'])</span>
                                    @else
                                        <span class="nex-profile-media-card-empty">@include('partials.icon', ['icon' => $section['icon']])</span>
                                    @endif
                                    <span class="nex-profile-media-card-count">{{ $items->count() }}</span>
                                </span>
                                <span class="nex-profile-media-card-copy"><strong>{{ $section['title'] }}</strong><small>{{ $items->count() }} {{ $items->count() === 1 ? 'item' : 'items' }}</small><i aria-hidden="true">›</i></span>
                            </a>
                        @endforeach
                    </div>
                </section>
                </div>

            <section class="nex-profile-posts" id="profile-posts">
                <div class="nex-profile-posts-heading"><span class="nex-section-eyebrow">PROFILE</span><h2>{{ $user->name }}’s posts</h2></div>
                @include('partials.posts-list', ['emptyMessage' => 'This user has not shared any posts yet.'])
            </section>
        </div>
    </main>
</x-app-layout>
