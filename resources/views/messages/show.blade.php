<x-app-layout>
    @vite(['resources/css/nex-notifications.css'])

    <main class="nex-chat-page">
        <section class="nex-chat-shell" aria-label="Conversation with {{ $user->name }}">
            <header class="nex-chat-header">
                <a class="nex-chat-back" href="{{ route('messages.index') }}" aria-label="Back to all messages">
                    <span aria-hidden="true">←</span><span>Messages</span>
                </a>

                <a class="nex-chat-person" href="{{ route('profiles.show', $user) }}">
                    <span class="nex-chat-avatar">
                        @if($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                        @if($user->is_online_friend ?? false)<i class="nex-chat-active-indicator" aria-label="Active now" title="Active now"></i>@endif
                    </span>
                    <span class="nex-chat-person-copy">
                        <strong>{{ $user->name }}</strong>
                        <small>{{ $user->username ? '@' . $user->username : 'NEX member' }}</small>
                        @if($user->last_active_label ?? false)<small class="nex-presence-copy {{ $user->is_online_friend ? 'is-online' : '' }}">{{ $user->last_active_label }}</small>@endif
                    </span>
                </a>

                <a class="nex-chat-profile" href="{{ route('profiles.show', $user) }}">View profile</a>
            </header>

            <div class="nex-chat-context"><span class="nex-chat-lock" aria-hidden="true">◇</span> Private conversation on NEX</div>

            <div class="nex-chat-thread" id="message-thread" aria-live="polite" aria-relevant="additions">
                @forelse($messages as $message)
                    @php($isMine = $message->sender_id === auth()->id())
                    <article class="nex-chat-row {{ $isMine ? 'is-mine' : 'is-theirs' }}" id="message-{{ $message->id }}">
                        @unless($isMine)
                            <span class="nex-chat-message-avatar" aria-hidden="true">
                                @if($user->profile_photo)<img src="{{ asset('storage/' . $user->profile_photo) }}" alt="">@else{{ strtoupper(substr($user->name, 0, 1)) }}@endif
                            </span>
                        @endunless
                        <div class="nex-chat-bubble {{ $isMine ? 'mine' : '' }}">
                            <p>{{ $message->body }}</p>
                            <small title="{{ $message->created_at->format('M j, Y g:i A') }}">
                                {{ $message->created_at->format('M j, g:i A') }}
                                @if($isMine && $message->read_at)<span class="nex-chat-read"> · Seen</span>@endif
                            </small>
                        </div>
                    </article>
                @empty
                    <div class="nex-chat-empty">
                        <span class="nex-chat-empty-avatar">
                            @if($user->profile_photo)<img src="{{ asset('storage/' . $user->profile_photo) }}" alt="">@else{{ strtoupper(substr($user->name, 0, 1)) }}@endif
                        </span>
                        <strong>You’re connected with {{ $user->name }}</strong>
                        <p>Start the conversation with a message.</p>
                    </div>
                @endforelse
            </div>

            @if($errors->any())
                <div class="nex-chat-error" role="alert">{{ $errors->first('body') }}</div>
            @endif
            @if(session('success'))
                <div class="nex-chat-success" role="status">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('messages.store', $user) }}" class="nex-chat-composer" data-chat-form>
                @csrf
                <label class="nex-chat-input-wrap">
                    <span class="sr-only">Write a message to {{ $user->name }}</span>
                    <textarea name="body" maxlength="5000" required rows="1" placeholder="Write a message…" data-chat-input>{{ old('body') }}</textarea>
                    <span class="nex-chat-char-count" data-chat-count>0 / 5000</span>
                </label>
                <button type="submit" data-chat-submit disabled><span>Send</span><span aria-hidden="true">↗</span></button>
                <p class="nex-chat-hint">Enter to send <span>·</span> Shift + Enter for a new line</p>
            </form>
        </section>
    </main>

    <script>
        (() => {
            const thread = document.getElementById('message-thread');
            const form = document.querySelector('[data-chat-form]');
            const input = document.querySelector('[data-chat-input]');
            const count = document.querySelector('[data-chat-count]');
            const submit = document.querySelector('[data-chat-submit]');

            if (thread) thread.scrollTop = thread.scrollHeight;
            if (!form || !input || !count || !submit) return;

            const updateComposer = () => {
                const length = input.value.length;
                count.textContent = `${length} / 5000`;
                submit.disabled = input.value.trim().length === 0;
                input.style.height = 'auto';
                input.style.height = `${Math.min(input.scrollHeight, 144)}px`;
            };

            input.addEventListener('input', updateComposer);
            input.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
                    event.preventDefault();
                    if (input.value.trim()) form.requestSubmit();
                }
            });
            updateComposer();
        })();
    </script>
</x-app-layout>
