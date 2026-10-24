<x-app-layout>
    @vite(['resources/css/nex-admin.css'])

    <main class="nex-admin-page">
        <div class="nex-admin-container">
            <header class="nex-admin-header">
                <div><span class="nex-admin-eyebrow">NEX TRUST & SAFETY</span><h1>Post reports</h1><p>Review reports and record the action taken on each account.</p></div>
                <a class="nex-admin-button nex-admin-button-secondary" href="{{ route('admin.accounts.index') }}">Manage accounts</a>
            </header>

            @if(session('success'))<div class="nex-admin-alert">{{ session('success') }}</div>@endif

            <nav class="nex-admin-tabs">
                @foreach(['pending' => 'Pending', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed', 'all' => 'All reports'] as $key => $label)
                    <a class="{{ $status === $key ? 'active' : '' }}" href="{{ route('admin.reports.index', ['status' => $key]) }}">{{ $label }}@if($key !== 'all' && $reportCounts->get($key, 0)) <span class="nex-admin-tab-count">{{ $reportCounts->get($key) }}</span>@endif</a>
                @endforeach
            </nav>

            <section class="nex-report-list">
                @forelse($reports as $report)
                    <article class="nex-admin-card">
                        <div class="nex-report-topline">
                            <div><span class="nex-admin-pill nex-admin-pill-{{ $report->status }}">{{ ucfirst($report->status) }}</span><span class="nex-report-date">Reported {{ $report->created_at->diffForHumans() }}</span></div>
                            <span class="nex-report-reason">{{ ucfirst(str_replace('_', ' ', $report->reason)) }}</span>
                        </div>

                        <div class="nex-report-parties">
                            <div><span>Reported by</span><strong>{{ $report->user->name }}</strong><small>{{ $report->user->email }}</small></div>
                            <div><span>Post author</span><strong>{{ $report->reportedUser?->name ?? $report->post?->user?->name ?? 'Account unavailable' }}</strong><small>{{ $report->reportedUser?->email ?? $report->post?->user?->email ?? '' }}</small></div>
                        </div>

                        <div class="nex-reported-post">
                            <span class="nex-admin-label">{{ $report->post_id ? 'REPORTED POST' : 'REPORTED ACCOUNT' }}</span>
                            @if($report->reported_content ?? $report->post?->content)<p>{{ $report->reported_content ?? $report->post?->content }}</p>@endif
                            @if($report->reported_media_path ?? $report->post?->media_path)
                                <a href="{{ asset('storage/' . ($report->reported_media_path ?? $report->post?->media_path)) }}" target="_blank" rel="noopener">Open attached {{ $report->reported_media_type ?? $report->post?->media_type }}</a>
                            @endif
                            @if($report->details)<p class="nex-reporter-details">Reporter’s note: {{ $report->details }}</p>@endif
                        </div>

                        @if($report->status === 'pending')
                            <form class="nex-admin-review-form" method="POST" action="{{ route('admin.reports.resolve', $report) }}">
                                @csrf
                                @method('PATCH')
                                <div class="nex-admin-fields">
                                    <label>Decision<select name="decision" required><option value="">Choose action</option><option value="dismiss">Dismiss report</option>@if($report->reportedUser || $report->post?->user)<option value="warning">Issue warning</option><option value="temporary_ban">Temporary ban</option><option value="permanent_ban">Permanent ban</option>@endif</select></label>
                                    <label>Violation<select name="violation"><option value="">Select violation</option><option value="spam">Spam</option><option value="harassment">Harassment</option><option value="hate_speech">Hate speech</option><option value="violence">Violence</option><option value="sexual_content">Sexual content</option><option value="impersonation">Impersonation</option><option value="other">Other</option></select></label>
                                    <label>Ban duration in days<input type="number" name="duration_days" min="1" max="3650" placeholder="Required for temporary ban"></label>
                                </div>
                                <label class="nex-admin-full-field">Decision note<textarea name="resolution_note" rows="3" maxlength="2000" required placeholder="Explain the decision and any account action"></textarea></label>
                                <button class="nex-admin-button" type="submit">Save review</button>
                            </form>
                        @else
                            <div class="nex-report-resolution"><strong>Decision: {{ ucfirst(str_replace('_', ' ', $report->decision ?? 'reviewed')) }}</strong><p>{{ $report->resolution_note }}</p><small>Reviewed by {{ $report->reviewer?->name ?? 'NEX staff' }} {{ $report->resolved_at?->diffForHumans() }}</small></div>
                        @endif
                    </article>
                @empty
                    <div class="nex-admin-empty"><span>✓</span><h2>No {{ $status === 'all' ? '' : $status }} reports</h2><p>Reports submitted by users will appear here for review.</p></div>
                @endforelse
            </section>

            <div class="nex-admin-pagination">{{ $reports->links() }}</div>
        </div>
    </main>
</x-app-layout>
