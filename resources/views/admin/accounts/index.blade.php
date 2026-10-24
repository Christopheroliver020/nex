<x-app-layout>
    @vite(['resources/css/nex-admin.css'])

    <main class="nex-admin-page">
        <div class="nex-admin-container">
            <header class="nex-admin-header">
                <div><span class="nex-admin-eyebrow">NEX TRUST & SAFETY</span><h1>Account moderation</h1><p>Apply, adjust, and lift account restrictions.</p></div>
                <a class="nex-admin-button nex-admin-button-secondary" href="{{ route('admin.reports.index') }}">Review reports</a>
            </header>

            @if(session('success'))<div class="nex-admin-alert">{{ session('success') }}</div>@endif

            <form class="nex-admin-search" method="GET" action="{{ route('admin.accounts.index') }}">
                <input type="search" name="q" value="{{ $search }}" placeholder="Search name, username, or email">
                <button class="nex-admin-button" type="submit">Search accounts</button>
            </form>

            <section class="nex-account-list">
                @forelse($accounts as $account)
                    <article class="nex-admin-card nex-account-card">
                        <div class="nex-account-heading">
                            <div class="nex-account-avatar">{{ strtoupper(substr($account->name, 0, 1)) }}</div>
                            <div><h2>{{ $account->name }} @if($account->username)<span>&#64;{{ $account->username }}</span>@endif</h2><p>{{ $account->email }}</p></div>
                            @if($account->is_admin)<span class="nex-admin-pill">NEX staff</span>
                            @elseif($account->is_permanently_banned)<span class="nex-admin-pill nex-admin-pill-banned">Permanently banned</span>
                            @elseif($account->banned_until && $account->banned_until->isFuture())<span class="nex-admin-pill nex-admin-pill-banned">Suspended until {{ $account->banned_until->format('M j, Y') }}</span>
                            @else<span class="nex-admin-pill nex-admin-pill-active">Active</span>@endif
                        </div>

                        @if(!$account->is_admin && $account->banned_until && $account->banned_until->isFuture() || (!$account->is_admin && $account->is_permanently_banned))
                            <div class="nex-current-ban"><strong>{{ ucfirst(str_replace('_', ' ', $account->ban_violation ?? 'policy violation')) }}</strong><p>{{ $account->ban_reason }}</p></div>
                        @endif

                        @if(!$account->is_admin && $account->id !== auth()->id())
                            <div class="nex-account-actions">
                                <form class="nex-admin-review-form" method="POST" action="{{ route('admin.accounts.ban', $account) }}">
                                    @csrf
                                    <h3>Apply account restriction</h3>
                                    <div class="nex-admin-fields">
                                        <label>Restriction<select name="type" required><option value="temporary">Temporary ban</option><option value="permanent">Permanent ban</option></select></label>
                                        <label>Duration in days<input type="number" name="duration_days" min="1" max="3650" placeholder="For temporary ban"></label>
                                        <label>Violation<select name="violation" required><option value="spam">Spam</option><option value="harassment">Harassment</option><option value="hate_speech">Hate speech</option><option value="violence">Violence</option><option value="sexual_content">Sexual content</option><option value="impersonation">Impersonation</option><option value="other">Other</option></select></label>
                                    </div>
                                    <label class="nex-admin-full-field">Reason<textarea name="reason" rows="2" maxlength="2000" required placeholder="Reason for this restriction"></textarea></label>
                                    <button class="nex-admin-button" type="submit">Apply restriction</button>
                                </form>

                                @if($account->banned_until && $account->banned_until->isFuture() && !$account->is_permanently_banned)
                                    <form class="nex-admin-review-form nex-ban-adjust-form" method="POST" action="{{ route('admin.accounts.ban.update', $account) }}">
                                        @csrf
                                        @method('PATCH')
                                        <h3>Change recovery date</h3>
                                        <div class="nex-admin-fields"><label>Days from now<input type="number" name="duration_days" min="1" max="3650" required></label><label>Note<input type="text" name="reason" maxlength="2000" placeholder="Optional audit note"></label></div>
                                        <button class="nex-admin-button nex-admin-button-secondary" type="submit">Update duration</button>
                                    </form>
                                @endif

                                @if($account->is_permanently_banned || ($account->banned_until && $account->banned_until->isFuture()))
                                    <form method="POST" action="{{ route('admin.accounts.restore', $account) }}" onsubmit="return confirm('Restore access for this account?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="nex-admin-button nex-admin-button-restore" type="submit">Restore account</button>
                                    </form>
                                @endif
                            </div>
                        @endif

                        @if($account->moderationActions->isNotEmpty())
                            <details class="nex-account-history"><summary>Moderation history ({{ $account->moderationActions->count() }})</summary>
                                <ul>@foreach($account->moderationActions as $action)<li><strong>{{ ucfirst(str_replace('_', ' ', $action->action)) }}</strong> · {{ $action->violation ? ucfirst(str_replace('_', ' ', $action->violation)) . ' · ' : '' }}{{ $action->reason }} <small>{{ $action->created_at->format('M j, Y g:i A') }} by {{ $action->admin?->name ?? 'Former staff' }}</small></li>@endforeach</ul>
                            </details>
                        @endif
                    </article>
                @empty
                    <div class="nex-admin-empty"><h2>No accounts found</h2><p>Try searching by a different name or email.</p></div>
                @endforelse
            </section>

            <div class="nex-admin-pagination">{{ $accounts->links() }}</div>
        </div>
    </main>
</x-app-layout>
