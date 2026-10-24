<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\NexActivityNotification;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function store(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Don't allow following yourself.
        if ($currentUser->id === $user->id) {
            return back();
        }

        abort_if($user->isAccountBanned(), 404);

        $attached = $currentUser->following()->syncWithoutDetaching([
            $user->id
        ]);

        if (count($attached['attached']) > 0) {
            $user->notify(new NexActivityNotification(
                action: 'follow',
                message: $currentUser->name . ' started following you.',
                actorId: $currentUser->id,
                actorName: $currentUser->name,
                url: '/users/' . $currentUser->getRouteKey(),
            ));
        }

        return back()->with(
            'success',
            'You are now following ' . $user->name . '.'
        );
    }

    public function destroy(Request $request, User $user)
    {
        $request->user()
            ->following()
            ->detach($user->id);

        return back()->with(
            'success',
            'You unfollowed ' . $user->name . '.'
        );
    }
}
