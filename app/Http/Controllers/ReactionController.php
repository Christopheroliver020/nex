<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Post;
use App\Models\PostReaction;
use App\Notifications\NexActivityNotification;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function post(Request $request, Post $post)
    {
        abort_unless(Post::visibleTo($request->user())->whereKey($post->id)->exists(), 404);

        $reaction = PostReaction::where('post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($reaction) {
            $reaction->delete();
        } else {
            PostReaction::create([
                'post_id' => $post->id,
                'user_id' => $request->user()->id,
                'type' => 'like',
            ]);

            if ($post->user_id !== $request->user()->id) {
                $post->user->notify(new NexActivityNotification(
                    action: 'post_reaction',
                    message: $request->user()->name . ' reacted to your post.',
                    actorId: $request->user()->id,
                    actorName: $request->user()->name,
                    url: '/newsfeed#post-' . $post->id,
                ));
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'reacted' => PostReaction::where('post_id', $post->id)
                    ->where('user_id', $request->user()->id)
                    ->exists(),
                'count' => $post->reactions()->count(),
            ]);
        }

        return back();
    }

    public function comment(Request $request, Comment $comment)
    {
        abort_unless(Post::visibleTo($request->user())->whereKey($comment->post_id)->exists(), 404);

        $reaction = CommentReaction::where('comment_id', $comment->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($reaction) {
            $reaction->delete();
        } else {
            CommentReaction::create([
                'comment_id' => $comment->id,
                'user_id' => $request->user()->id,
                'type' => 'like',
            ]);

            if ($comment->user_id !== $request->user()->id) {
                $comment->user->notify(new NexActivityNotification(
                    action: 'comment_reaction',
                    message: $request->user()->name . ' reacted to your comment.',
                    actorId: $request->user()->id,
                    actorName: $request->user()->name,
                    url: '/newsfeed#post-' . $comment->post_id,
                    context: ['comment_id' => $comment->id],
                ));
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'reacted' => CommentReaction::where('comment_id', $comment->id)
                    ->where('user_id', $request->user()->id)
                    ->exists(),
                'count' => $comment->reactions()->count(),
            ]);
        }

        return back();
    }
}
