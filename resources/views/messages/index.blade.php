<x-app-layout>
    @vite(['resources/css/nex-notifications.css'])

    <main class="nex-inbox-page">
        <div class="nex-inbox-wrap">
            <header class="nex-inbox-heading">
                <div>
                    <span class="nex-notifications-eyebrow">YOUR CONNECTIONS</span>
                    <h1>Messages</h1>
                    <p>Private conversations with people on NEX.</p>
                </div>
                <span class="nex-inbox-total">{{ $conversations->count() }} {{ $conversations->count() === 1 ? 'conversation' : 'conversations' }}</span>
            </header>

            @if($conversations->isNotEmpty())
                <section class="nex-inbox-tools" aria-label="Filter conversations">
                    <label class="nex-inbox-search">
                        <span aria-hidden="true">⌕</span>
                        <input type="search" placeholder="Search conversations" aria-label="Search conversations" data-inbox-search>
                    </label>
                    <div class="nex-inbox-filters" role="group" aria-label="Conversation filter">
                        <button type="button" class="is-active" data-inbox-filter="all" aria-pressed="true">All</button>
                        <button type="button" data-inbox-filter="unread" aria-pressed="false">Unread</button>
                    </div>
                </section>
            @endif

            <section class="nex-inbox-list" aria-label="Your conversations" data-inbox-list>
                @forelse($conversations as $message)
                    @php
                        $person = $message->sender_id === auth()->id() ? $message->recipient : $message->sender;
                        $isUnread = $message->recipient_id === auth()->id() && is_null($message->read_at);
                    @endphp
                    <a class="nex-inbox-conversation {{ $isUnread ? 'is-unread' : '' }}"
                       href="{{ route('messages.show', $person) }}"
                       data-conversation
                       data-unread="{{ $isUnread ? 'true' : 'false' }}"
                       data-search="{{ strtolower($person->name . ' ' . ($person->username ?? '') . ' ' . $message->body) }}">
                        <span class="nex-inbox-avatar">
                            @if($person->profile_photo)
                                <img src="{{ asset('storage/' . $person->profile_photo) }}" alt="">
                            @else
                                {{ strtoupper(substr($person->name, 0, 1)) }}
                            @endif
                            @if($person->is_online_friend ?? false)<i class="is-online" aria-label="Active now" title="Active now"></i>@endif
                        </span>
                        <span class="nex-inbox-copy">
                            <span class="nex-inbox-line"><strong>{{ $person->name }}</strong><time datetime="{{ $message->created_at->toISOString() }}">{{ $message->created_at->diffForHumans() }}</time></span>
                            <span class="nex-inbox-preview">{{ $message->sender_id === auth()->id() ? 'You: ' : '' }}{{ $message->body }}</span>
                            <span class="nex-inbox-username">{{ $person->username ? '@' . $person->username : 'NEX member' }}</span>
                            @if($person->last_active_label ?? false)<span class="nex-presence-copy {{ $person->is_online_friend ? 'is-online' : '' }}">{{ $person->last_active_label }}</span>@endif
                        </span>
                        @if($isUnread)<span class="nex-inbox-unread-dot" aria-label="Unread conversation"></span>@else<span class="nex-inbox-chevron" aria-hidden="true">›</span>@endif
                    </a>
                @empty
                    <div class="nex-inbox-empty">
                        <span class="nex-inbox-empty-icon" aria-hidden="true">✉</span>
                        <h2>Your conversations start here</h2>
                        <p>Visit someone’s profile and send a message to start chatting.</p>
                    </div>
                @endforelse
                @if($conversations->isNotEmpty())
                    <div class="nex-inbox-no-results" data-inbox-empty hidden>No conversations match your search.</div>
                @endif
            </section>
        </div>
    </main>

    @if($conversations->isNotEmpty())
        <script>
            (() => {
                const search = document.querySelector('[data-inbox-search]');
                const cards = Array.from(document.querySelectorAll('[data-conversation]'));
                const buttons = Array.from(document.querySelectorAll('[data-inbox-filter]'));
                const empty = document.querySelector('[data-inbox-empty]');
                let filter = 'all';

                const update = () => {
                    const query = search.value.trim().toLocaleLowerCase();
                    let visible = 0;
                    cards.forEach((card) => {
                        const matchesText = card.dataset.search.toLocaleLowerCase().includes(query);
                        const matchesFilter = filter === 'all' || card.dataset.unread === 'true';
                        card.hidden = !(matchesText && matchesFilter);
                        if (!card.hidden) visible++;
                    });
                    empty.hidden = visible !== 0;
                    empty.textContent = query || filter === 'unread' ? 'No conversations match this filter.' : 'No conversations yet.';
                };

                search.addEventListener('input', update);
                buttons.forEach((button) => button.addEventListener('click', () => {
                    filter = button.dataset.inboxFilter;
                    buttons.forEach((item) => {
                        const active = item === button;
                        item.classList.toggle('is-active', active);
                        item.setAttribute('aria-pressed', active ? 'true' : 'false');
                    });
                    update();
                }));
            })();
        </script>
    @endif
</x-app-layout>
