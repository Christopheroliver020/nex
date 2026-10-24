<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Notifications\NexActivityNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        abort_unless(Post::visibleTo($request->user())->whereKey($post->id)->exists(), 404);

        $validated = $request->validate([
            'content' => [
                'required',
                'string',
                'max:2000',
            ],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:comments,id',
            ],
        ]);

        $parent = null;
        if (!empty($validated['parent_id'])) {
            $parent = Comment::findOrFail($validated['parent_id']);

            if ($parent->post_id !== $post->id) {
                abort(403);
            }
        }

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        $actor = $request->user();
        $notificationUrl = '/newsfeed#post-' . $post->id;
        if ($post->user_id !== $actor->id) {
            $post->user->notify(new NexActivityNotification(
                action: 'comment',
                message: $actor->name . ' commented on your post.',
                actorId: $actor->id,
                actorName: $actor->name,
                url: $notificationUrl,
                context: ['post_id' => $post->id, 'comment_id' => $comment->id],
            ));
        }
        if ($comment->parent_id && $parent->user_id !== $actor->id && $parent->user_id !== $post->user_id) {
            $parent->user->notify(new NexActivityNotification(
                action: 'reply',
                message: $actor->name . ' replied to your comment.',
                actorId: $actor->id,
                actorName: $actor->name,
                url: $notificationUrl,
                context: ['post_id' => $post->id, 'comment_id' => $comment->id, 'parent_id' => $parent->id],
            ));
        }

        return back()->with('success', 'Comment added.');
    }

    public function destroy(Request $request, Comment $comment)
    {
        abort_unless(Post::visibleTo($request->user())->whereKey($comment->post_id)->exists(), 404);

        if ($comment->user_id !== $request->user()->id) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
