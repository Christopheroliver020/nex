<x-app-layout>
    <main class="nex-myday-view-page">
        <a class="nex-myday-view-back" href="{{ route('myday.index') }}">← <span>All My Days</span></a>
        <nav class="nex-myday-browser" aria-label="Browse stories">
            @foreach($storyUsers as $storyUser)
                <a class="nex-myday-browser-user {{ $storyUser->viewed_by_me ? 'is-viewed' : 'is-unviewed' }} {{ $storyUser->user_id === $story->user_id ? 'is-current' : '' }}" href="{{ route('myday.show', $storyUser) }}">
                    <span>@if($storyUser->user->profile_photo)<img src="{{ asset('storage/' . $storyUser->user->profile_photo) }}" alt="">@else{{ strtoupper(substr($storyUser->user->name, 0, 1)) }}@endif</span>
                    <small>{{ $storyUser->user_id === Auth::id() ? 'Your Day' : $storyUser->user->name }}</small>
                </a>
            @endforeach
        </nav>
        <article class="nex-myday-view-card">
            <header class="nex-myday-view-header">
                <a class="nex-myday-user-avatar" href="{{ route('profiles.show', $story->user) }}">
                    @if($story->user->profile_photo)<img src="{{ asset('storage/' . $story->user->profile_photo) }}" alt="">@else{{ strtoupper(substr($story->user->name, 0, 1)) }}@endif
                </a>
                <div><a href="{{ route('profiles.show', $story->user) }}">{{ $story->user->name }}</a><span>{{ $story->created_at->diffForHumans() }} · expires {{ $story->expires_at->diffForHumans() }}</span></div>
                @if($story->user_id === Auth::id())
                    <form method="POST" action="{{ route('myday.destroy', $story) }}" data-confirm-delete data-confirm-title="Delete this My Day story?" data-confirm-message="This story will be permanently removed. You cannot undo this action." data-confirm-action="Delete story">@csrf @method('DELETE')<button type="submit">Delete</button></form>
                @endif
            </header>

            <div class="nex-myday-view-media">
                @if($story->media_type === 'image')
                    <img src="{{ route('myday.media', [$story, 'media']) }}" alt="My Day photo shared by {{ $story->user->name }}">
                @else
                    <video src="{{ route('myday.media', [$story, 'media']) }}" controls playsinline preload="metadata" @if($story->music_path) muted @endif></video>
                @endif
                @if($story->caption)<p class="nex-myday-view-caption">{{ $story->caption }}</p>@endif
                @if($olderStory)<a class="nex-myday-story-nav older" href="{{ route('myday.show', $olderStory) }}" aria-label="View older story">‹</a>@endif
                @if($newerStory)<a class="nex-myday-story-nav newer" href="{{ route('myday.show', $newerStory) }}" aria-label="View newer story">›</a>@endif
            </div>

            @if($story->music_path)
                <section class="nex-myday-music"><div><span>BACKGROUND MUSIC</span><strong>Play the soundtrack</strong></div><audio controls preload="none"><source src="{{ route('myday.media', [$story, 'music']) }}"></audio></section>
            @endif
            @if($story->lyrics)<section class="nex-myday-lyrics"><span>LYRICS</span><p>{{ $story->lyrics }}</p></section>@endif
        </article>
    </main>
</x-app-layout>
