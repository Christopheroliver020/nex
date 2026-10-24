<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FriendPresence
{
    /**
     * Return active user IDs that are mutual friends with the viewer.
     * A database session counts as active for five minutes after its last request.
     *
     * @param  iterable<int|string>  $candidateIds
     * @return Collection<int, int>
     */
    public static function onlineFriendIds(User $viewer, iterable $candidateIds): Collection
    {
        return self::lastSeenForFriends($viewer, $candidateIds)
            ->filter(fn ($lastActivity) => $lastActivity >= now()->subMinutes(5)->timestamp)
            ->keys()
            ->values();
    }

    /** Return the most recent saved session activity for each mutual friend. */
    public static function lastSeenForFriends(User $viewer, iterable $candidateIds): Collection
    {
        $candidateIds = collect($candidateIds)->filter(fn ($id) => is_numeric($id))->map(fn ($id) => (int) $id)->unique()->values();
        if ($candidateIds->isEmpty()) {
            return collect();
        }

        $followingIds = $viewer->following()->whereIn('users.id', $candidateIds)->pluck('users.id');
        if ($followingIds->isEmpty()) {
            return collect();
        }

        $friendIds = $viewer->followers()->whereIn('users.id', $followingIds)->pluck('users.id');
        if ($friendIds->isEmpty()) {
            return collect();
        }

        return DB::table('sessions')
            ->whereIn('user_id', $friendIds)
            ->selectRaw('user_id, MAX(last_activity) as last_activity')
            ->groupBy('user_id')
            ->get()
            ->mapWithKeys(fn ($session) => [(int) $session->user_id => (int) $session->last_activity]);
    }

    public static function label(int $lastActivity): string
    {
        if ($lastActivity >= now()->subMinutes(5)->timestamp) {
            return 'Active now';
        }

        return 'Last active ' . Carbon::createFromTimestamp($lastActivity)->diffForHumans();
    }
}
