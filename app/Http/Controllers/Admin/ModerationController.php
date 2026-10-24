<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModerationAction;
use App\Models\PostReport;
use App\Models\User;
use App\Notifications\NexActivityNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModerationController extends Controller
{
    private const VIOLATIONS = [
        'spam', 'harassment', 'hate_speech', 'violence',
        'sexual_content', 'impersonation', 'other',
    ];

    public function reports(Request $request): View
    {
        $status = $request->query('status', 'pending');
        abort_unless(in_array($status, ['pending', 'resolved', 'dismissed', 'all'], true), 404);

        $reports = PostReport::with(['post.user', 'reportedUser', 'user', 'reviewer'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $reportCounts = PostReport::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.reports.index', compact('reports', 'status', 'reportCounts'));
    }

    public function accounts(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $accounts = User::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            }))
            ->with('moderationActions.admin')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.accounts.index', compact('accounts', 'search'));
    }

    public function resolveReport(Request $request, PostReport $postReport): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['dismiss', 'warning', 'temporary_ban', 'permanent_ban'])],
            'violation' => ['required_if:decision,warning,temporary_ban,permanent_ban', 'nullable', Rule::in(self::VIOLATIONS)],
            'duration_days' => ['required_if:decision,temporary_ban', 'nullable', 'integer', 'min:1', 'max:3650'],
            'resolution_note' => ['required', 'string', 'max:2000'],
        ]);

        $target = $postReport->reportedUser ?? $postReport->post?->user;
        abort_if(!$target && $validated['decision'] !== 'dismiss', 422, 'The reported account no longer exists. This report can only be dismissed.');
        abort_if($target?->is_admin, 403, 'NEX staff accounts cannot be moderated from a user report.');

        DB::transaction(function () use ($request, $postReport, $target, $validated) {
            $postReport->forceFill([
                'status' => $validated['decision'] === 'dismiss' ? 'dismissed' : 'resolved',
                'decision' => $validated['decision'],
                'violation' => $validated['violation'] ?? null,
                'resolution_note' => $validated['resolution_note'],
                'reviewed_by' => $request->user()->id,
                'resolved_at' => now(),
            ])->save();

            if ($validated['decision'] === 'warning' && $target) {
                $this->recordAction($target, $request->user(), $postReport, [
                    'action' => 'warning',
                    'violation' => $validated['violation'] ?? null,
                    'reason' => $validated['resolution_note'],
                ]);
                $target->notify(new NexActivityNotification(
                    action: 'admin_warning',
                    message: 'NEX sent you a warning after reviewing a report about your account or content.',
                    actorId: $request->user()->id,
                    actorName: 'NEX moderation',
                    url: route('notifications.index'),
                    context: [
                        'violation' => str_replace('_', ' ', $validated['violation']),
                        'message' => $validated['resolution_note'],
                        'report_id' => $postReport->id,
                    ],
                ));
            } elseif ($target && in_array($validated['decision'], ['temporary_ban', 'permanent_ban'], true)) {
                $this->applyBan(
                    $target,
                    $request->user(),
                    $validated['decision'],
                    $validated['violation'] ?? 'other',
                    $validated['resolution_note'],
                    $validated['decision'] === 'temporary_ban' ? (int) $validated['duration_days'] : null,
                    $postReport,
                );
            }
        });

        return back()->with('success', 'Report reviewed and action recorded.');
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id || $user->is_admin, 403);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['temporary', 'permanent'])],
            'duration_days' => ['required_if:type,temporary', 'nullable', 'integer', 'min:1', 'max:3650'],
            'violation' => ['required', Rule::in(self::VIOLATIONS)],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $this->applyBan(
            $user,
            $request->user(),
            $validated['type'] === 'permanent' ? 'permanent_ban' : 'temporary_ban',
            $validated['violation'],
            $validated['reason'],
            $validated['type'] === 'temporary' ? (int) $validated['duration_days'] : null,
        );

        return back()->with('success', 'Account restriction applied.');
    }

    public function changeBan(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is_admin, 403);
        abort_unless($user->banned_until && !$user->is_permanently_banned, 422, 'Only a temporary ban can be extended or changed.');

        $validated = $request->validate([
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $expiresAt = now()->addDays((int) $validated['duration_days']);
        $user->forceFill(['banned_until' => $expiresAt])->save();

        $this->recordAction($user, $request->user(), null, [
            'action' => 'duration_changed',
            'violation' => $user->ban_violation,
            'reason' => $validated['reason'] ?? 'Temporary ban duration changed.',
            'duration_days' => (int) $validated['duration_days'],
            'expires_at' => $expiresAt,
        ]);

        return back()->with('success', 'Temporary ban recovery date updated.');
    }

    public function restore(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403);

        $wasBanned = $user->is_permanently_banned || $user->banned_until !== null;
        $user->forceFill([
            'is_permanently_banned' => false,
            'banned_until' => null,
            'ban_violation' => null,
            'ban_reason' => null,
        ])->save();

        if ($wasBanned) {
            $this->recordAction($user, $request->user(), null, [
                'action' => 'reinstated',
                'reason' => 'Account restored by NEX staff.',
            ]);
        }

        return back()->with('success', 'Account access restored.');
    }

    private function applyBan(User $user, User $admin, string $type, string $violation, string $reason, ?int $days, ?PostReport $report = null): void
    {
        $permanent = $type === 'permanent_ban';
        $expiresAt = $permanent ? null : now()->addDays($days ?? 1);

        $user->forceFill([
            'is_permanently_banned' => $permanent,
            'banned_until' => $expiresAt,
            'ban_violation' => $violation,
            'ban_reason' => $reason,
        ])->save();

        $this->recordAction($user, $admin, $report, [
            'action' => $type,
            'violation' => $violation,
            'reason' => $reason,
            'duration_days' => $days,
            'expires_at' => $expiresAt,
        ]);
    }

    private function recordAction(User $user, User $admin, ?PostReport $report, array $attributes): void
    {
        $user->moderationActions()->create(array_merge($attributes, [
            'admin_id' => $admin->id,
            'post_report_id' => $report?->id,
        ]));
    }
}
