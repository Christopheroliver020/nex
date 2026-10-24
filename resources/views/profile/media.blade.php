<x-app-layout>
    @vite(['resources/css/nex-profile.css', 'resources/css/nex-feed.css'])

    <main class="nex-profile-page">
        <div class="nex-profile-container nex-profile-media-page">
            <a class="nex-profile-media-back" href="{{ route('profiles.show', $user) }}">← <span>Back to {{ $user->name }}’s profile</span></a>
            <header class="nex-profile-media-heading">
                <span class="nex-section-eyebrow">{{ strtoupper($mediaTitle) }}</span>
                <h1>{{ $user->name }}’s {{ strtolower($mediaTitle) }}</h1>
                <p>{{ $mediaItems->count() }} {{ $mediaItems->count() === 1 ? 'item' : 'items' }} in this collection</p>
            </header>

            @if($mediaItems->isNotEmpty())
                <section class="nex-profile-media-grid" aria-label="{{ $mediaTitle }}">
                    @foreach($mediaItems as $item)
                        <article class="nex-profile-photo-tile">
                            @if($item['media_type'] === 'video')
                                <video controls playsinline preload="metadata" aria-label="Video shared by {{ $user->name }}"><source src="{{ asset('storage/' . $item['path']) }}"></video>
                            @else
                                <button type="button" data-image-viewer aria-label="View {{ strtolower($user->name) }}’s {{ strtolower($item['label']) }}">
                                    <img src="{{ asset('storage/' . $item['path']) }}" alt="{{ $item['label'] }} shared by {{ $user->name }}" loading="lazy">
                                </button>
                            @endif
                            <span><strong>{{ $item['label'] }}</strong><small>{{ $item['created_at']->diffForHumans() }}</small></span>
                        </article>
                    @endforeach
                </section>
            @else
                <section class="nex-profile-media-empty">
                    <span aria-hidden="true">◫</span>
                    <h2>No {{ strtolower($mediaTitle) }} yet</h2>
                    <p>When {{ $user->name }} shares {{ strtolower($mediaTitle) }}, they’ll appear here.</p>
                </section>
            @endif
        </div>
    </main>
</x-app-layout>
