<x-app-layout>
    <main class="nex-myday-page">
        <div class="nex-myday-wrap">
            <header class="nex-myday-heading">
                <div><span class="nex-eyebrow">SHARE A MOMENT</span><h1>My Day</h1><p>Photos and videos stay up for 24 hours. Videos can be up to one minute.</p></div>
                <a href="{{ route('newsfeed') }}" class="nex-myday-back">Back to newsfeed</a>
            </header>

            @if(session('success'))<div class="nex-myday-success">{{ session('success') }}</div>@endif

            <section class="nex-myday-compose-card">
                <div class="nex-myday-section-heading"><span class="nex-myday-section-icon">+</span><div><h2>Add to your day</h2><p>Choose a photo or a short video. Add a caption, music, and lyrics if you like.</p></div></div>

                <form method="POST" action="{{ route('myday.store') }}" enctype="multipart/form-data" class="nex-myday-form" data-myday-form>
                    @csrf
                    <label class="nex-myday-upload" for="myday-media">
                        <span class="nex-myday-upload-icon">↑</span><strong>Choose a photo or video</strong><small>JPG, PNG, WEBP, MP4, MOV, or WEBM · Max 30 MB · Video max 1 minute</small>
                        <input id="myday-media" type="file" name="media" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm" required data-myday-media>
                    </label>
                    @error('media')<p class="nex-myday-error">{{ $message }}</p>@enderror
                    <div class="nex-myday-preview" data-myday-preview hidden></div>

                    <label class="nex-myday-field">Caption <span>Optional</span><textarea name="caption" maxlength="500" rows="2" placeholder="Add a caption…">{{ old('caption') }}</textarea></label>
                    @error('caption')<p class="nex-myday-error">{{ $message }}</p>@enderror

                    <div class="nex-myday-two-fields">
                        <label class="nex-myday-field">Background music <span>Optional · MP3, M4A, AAC, WAV, OGG</span><input type="file" name="music" accept="audio/mpeg,audio/mp4,audio/aac,audio/wav,audio/ogg,.mp3,.m4a,.aac,.wav,.ogg"></label>
                        <label class="nex-myday-field">Who can see it? <span>Optional · defaults to Friends only</span><select name="audience"><option value="friends" @selected(old('audience', 'friends') === 'friends')>Friends only</option><option value="public" @selected(old('audience') === 'public')>Public</option></select></label>
                    </div>
                    @error('music')<p class="nex-myday-error">{{ $message }}</p>@enderror

                    <label class="nex-myday-field">Lyrics <span>Optional · up to 2,000 characters</span><textarea name="lyrics" maxlength="2000" rows="3" placeholder="Add lyrics to display with your music…">{{ old('lyrics') }}</textarea></label>
                    @error('lyrics')<p class="nex-myday-error">{{ $message }}</p>@enderror

                    <div class="nex-myday-form-footer"><span>My Day expires automatically 24 hours after upload.</span><button type="submit">Share My Day</button></div>
                </form>
            </section>

            <section class="nex-myday-active">
                <div class="nex-myday-section-heading"><span class="nex-myday-section-icon">◷</span><div><h2>Active My Days</h2><p>Recent moments from you and people you follow back.</p></div></div>
                <div class="nex-myday-active-grid">
                    @forelse($storiesByUser as $storyGroup)
                        @php($story = $storyGroup->first())
                        <article class="nex-myday-active-card">
                            <a class="nex-myday-active-preview" href="{{ route('myday.show', $story) }}">
                                @if($story->media_type === 'image')<img src="{{ route('myday.media', [$story, 'media']) }}" alt="">@else<span class="nex-myday-video-thumb">@include('partials.icon', ['icon' => 'play'])</span>@endif
                                <span class="nex-myday-preview-play">@include('partials.icon', ['icon' => 'play'])</span>
                            </a>
                            <div class="nex-myday-active-copy">
                                <div><strong>{{ $story->user_id === Auth::id() ? 'Your My Day' : $story->user->name }}</strong><span>{{ $storyGroup->count() }} {{ $storyGroup->count() === 1 ? 'story' : 'stories' }}</span></div>
                                <small>Expires {{ $story->expires_at->diffForHumans() }}</small>
                                <a class="nex-myday-open-link" href="{{ route('myday.show', $story) }}">View story</a>
                                @if($story->user_id === Auth::id())
                                    <details class="nex-myday-feature-form"><summary>Feature on profile</summary>
                                        <form method="POST" action="{{ route('myday.highlights.store') }}">@csrf<input type="hidden" name="story_id" value="{{ $story->id }}"><label>Title<input name="title" maxlength="50" required placeholder="Summer trip"></label><label>Text below the feature<input name="description" maxlength="160" placeholder="A few favorite moments"></label><button type="submit">Add featured story</button></form>
                                        @foreach(Auth::user()->myDayHighlights as $highlight)<form method="POST" action="{{ route('myday.highlights.stories.store', $highlight) }}">@csrf<input type="hidden" name="story_id" value="{{ $story->id }}"><button type="submit">Add to {{ $highlight->title }}</button></form>@endforeach
                                    </details>
                                    <form method="POST" action="{{ route('myday.destroy', $story) }}" data-confirm-delete data-confirm-title="Delete this My Day story?" data-confirm-message="This story will be permanently removed. You cannot undo this action." data-confirm-action="Delete story">@csrf @method('DELETE')<button type="submit">Delete latest</button></form>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="nex-myday-empty"><span>◷</span><strong>No active stories yet</strong><p>Share your first moment above.</p></div>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
</x-app-layout>
