<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Newsfeed posts
        |--------------------------------------------------------------------------
        |
        | Get posts from all users.
        |
        */

        $posts = Post::with('user')
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | People to follow
        |--------------------------------------------------------------------------
        |
        | Don't show the currently logged-in user.
        |
        | Don't show users that the current user already follows.
        |
        */

        $followingIds = $user->following()
            ->pluck('users.id');

        $peopleToFollow = User::query()
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $followingIds)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'user',
            'posts',
            'peopleToFollow'
        ));
    }
}
