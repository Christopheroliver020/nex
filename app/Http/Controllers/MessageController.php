<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\NexActivityNotification;
use App\Support\FriendPresence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $conversations = Message::query()
            ->where(fn ($query) => $query->where('sender_id', $userId)->orWhere('recipient_id', $userId))
            ->with(['sender', 'recipient'])
            ->latest()
            ->get()
            ->unique(fn (Message $message) => $message->sender_id === $userId ? $message->recipient_id : $message->sender_id)
            ->values();

        $otherUserIds = $conversations->map(fn (Message $message) => $message->sender_id === $userId ? $message->recipient_id : $message->sender_id);
        $lastSeenByFriend = FriendPresence::lastSeenForFriends($request->user(), $otherUserIds);
        foreach ($conversations as $message) {
            $person = $message->sender_id === $userId ? $message->recipient : $message->sender;
            $lastSeen = $lastSeenByFriend->get($person->id);
            $person->setAttribute('last_active_label', $lastSeen ? FriendPresence::label($lastSeen) : null);
            $person->setAttribute('is_online_friend', $person->last_active_label === 'Active now');
        }

        return view('messages.index', compact('conversations'));
    }

    public function show(Request $request, User $user): View
    {
        abort_if($user->id === $request->user()->id || $user->isAccountBanned(), 404);
        $lastSeenByFriend = FriendPresence::lastSeenForFriends($request->user(), [$user->id]);
        $lastSeen = $lastSeenByFriend->get($user->id);
        $user->setAttribute('last_active_label', $lastSeen ? FriendPresence::label($lastSeen) : null);
        $user->setAttribute('is_online_friend', $user->last_active_label === 'Active now');
        $messages = Message::query()
            ->where(fn ($query) => $query->where('sender_id', $request->user()->id)->where('recipient_id', $user->id))
            ->orWhere(fn ($query) => $query->where('sender_id', $user->id)->where('recipient_id', $request->user()->id))
            ->oldest()
            ->get();

        Message::where('sender_id', $user->id)
            ->where('recipient_id', $request->user()->id)
            ->whereNull('read_at')->update(['read_at' => now()]);

        return view('messages.show', compact('user', 'messages'));
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id || $user->isAccountBanned(), 404);
        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $message = Message::create([
            'sender_id' => $request->user()->id,
            'recipient_id' => $user->id,
            'body' => $validated['body'],
        ]);

        $user->notify(new NexActivityNotification(
            action: 'message',
            message: $request->user()->name . ' sent you a message.',
            actorId: $request->user()->id,
            actorName: $request->user()->name,
            url: '/messages/' . $request->user()->getRouteKey(),
            context: ['message_id' => $message->id],
        ));

        return redirect()->route('messages.show', $user)->with('success', 'Message sent.');
    }
}
