<section class="nex-myday-strip" aria-label="My Day stories">
    <div class="nex-myday-strip-heading">
        <div><span class="nex-eyebrow">YOUR MOMENTS</span><h2>My Day</h2></div>
        <a href="{{ route('myday.index') }}">Create or manage</a>
    </div>
    <div class="nex-myday-strip-items">
        <a class="nex-myday-add-card" href="{{ route('myday.index') }}">
            <span class="nex-myday-add-avatar">
                @if(Auth::user()->profile_photo)<img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="">@else<span class="nex-myday-add-initial">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>@endif
                <i>+</i>
            </span>
            <strong>Add to My Day</strong>
        </a>
        @foreach($myDayStories as $story)
            <a class="nex-myday-story-card {{ $story->viewed_by_me ? 'is-viewed' : 'is-unviewed' }}" href="{{ route('myday.show', $story) }}">
                <span class="nex-myday-story-ring {{ $story->viewed_by_me ? 'is-viewed' : 'is-unviewed' }}">
                    @if($story->media_type === 'image')
                        <img src="{{ route('myday.media', [$story, 'media']) }}" alt="">
                    @else
                        <span class="nex-myday-video-thumb">@include('partials.icon', ['icon' => 'play'])</span>
                    @endif
                </span>
                <strong>{{ $story->user_id === Auth::id() ? 'Your My Day' : $story->user->name }}</strong>
                <small>{{ $story->user_id === Auth::id() ? 'Your story' : ($story->viewed_by_me ? 'Viewed' : 'New') }}</small>
            </a>
        @endforeach
    </div>
</section>
