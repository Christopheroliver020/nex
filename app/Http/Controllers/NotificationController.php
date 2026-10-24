<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(10);
        $actorIds = $notifications->getCollection()
            ->map(fn ($notification) => $notification->data['actor_id'] ?? null)
            ->filter(fn ($id) => is_numeric($id))
            ->unique()
            ->values();
        $actors = $actorIds->isEmpty()
            ? collect()
            : User::query()->whereIn('id', $actorIds)->get(['id', 'name', 'profile_photo'])->keyBy('id');

        $notifications->getCollection()->each(function ($notification) use ($actors) {
            $notification->actor = $actors->get((int) ($notification->data['actor_id'] ?? 0));
        });

        return view('notifications.index', compact('notifications'));
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        $url = $item->data['url'] ?? route('notifications.index');
        abort_unless(is_string($url) && str_starts_with($url, '/') && !str_starts_with($url, '//'), 404);

        return redirect($url);
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
