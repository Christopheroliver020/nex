<x-app-layout>
    @vite(['resources/css/nex-notifications.css'])
    <main class="nex-notifications-page">
        <div class="nex-notifications-wrap">
            <header class="nex-notifications-heading">
                <div><span class="nex-notifications-eyebrow">YOUR ACTIVITY</span><h1>Notifications</h1><p>Updates about your NEX account and posts.</p></div>
                @if(auth()->user()->unreadNotifications()->exists())
                    <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="nex-notifications-read-all">Mark all read</button></form>
                @endif
            </header>
            @if(session('success'))<div class="nex-notifications-success">{{ session('success') }}</div>@endif

            <section class="nex-notification-list" aria-label="Your notifications">
                @forelse($notifications as $notification)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button class="nex-notification-row {{ $notification->read_at ? '' : 'unread' }}" type="submit">
                            <span class="nex-notification-avatar">
                                @if($notification->actor?->profile_photo)
                                    <img src="{{ asset('storage/' . $notification->actor->profile_photo) }}" alt="{{ $notification->actor->name }}" loading="lazy">
                                @elseif($notification->data['actor_id'] ?? false)
                                    {{ strtoupper(substr($notification->data['actor_name'] ?? 'N', 0, 1)) }}
                                @else
                                    N
                                @endif
                            </span>
                            <span class="nex-notification-copy">
                                <strong>{{ $notification->data['message'] ?? 'You have a new NEX notification.' }}</strong>
                                @if(($notification->data['action'] ?? '') === 'admin_warning')
                                    <span class="nex-notification-admin-note"><b>{{ str($notification->data['context']['violation'] ?? 'community guidelines')->title() }}</b>{{ $notification->data['context']['message'] ?? '' }}</span>
                                @endif
                                <small>{{ $notification->created_at->diffForHumans() }}</small>
                            </span>
                            @if(!$notification->read_at)<i aria-label="Unread"></i>@endif
                        </button>
                    </form>
                @empty
                    <div class="nex-notifications-empty"><span>♡</span><h2>You’re all caught up</h2><p>New follows, reactions, comments, messages, and moderation updates will appear here.</p></div>
                @endforelse
            </section>
            @if($notifications->hasPages())
                <nav class="nex-notifications-pagination" aria-label="Notification pages">
                    <p>Showing <strong>{{ $notifications->firstItem() }}</strong>–<strong>{{ $notifications->lastItem() }}</strong> of <strong>{{ $notifications->total() }}</strong> notifications</p>
                    {{ $notifications->onEachSide(1)->links() }}
                </nav>
            @endif
        </div>
    </main>
</x-app-layout>
