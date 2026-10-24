<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReport;
use App\Models\MyDayStory;
use App\Models\User;
use App\Models\PostShare;
use App\Notifications\NexActivityNotification;
use App\Support\FriendPresence;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function reels(Request $request)
    {
        $followingIds = $request->user()->following()->pluck('users.id')->all();
        $posts = Post::visibleTo($request->user())
            ->where('media_type', 'video')
            ->whereNotNull('media_path')
            ->with([
                'user',
                'reactions',
                'comments.user',
                'comments.reactions',
                'comments.replies.user',
                'comments.replies.reactions',
            ])
            ->withCount(['reactions', 'comments', 'shares'])
            ->latest()
            ->paginate(10);

        return view('reels.index', compact('posts', 'followingIds'));
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $posts = Post::visibleTo($user)->with([
            'user',
            'reactions',
            'comments.user',
            'comments.reactions',
            'comments.replies.user',
            'comments.replies.reactions',
        ])
        ->withCount([
            'reactions',
            'comments',
            'shares',
        ])
        ->latest()
        ->get();

        $visiblePeople = $posts->flatMap(function (Post $post) {
            return collect([$post->user_id])->merge($post->comments->flatMap(function ($comment) {
                return collect([$comment->user_id])->merge($comment->replies->pluck('user_id'));
            }));
        })->unique()->values();
        $lastSeenByFriend = FriendPresence::lastSeenForFriends($user, $visiblePeople);
        foreach ($posts as $post) {
            $post->user->setAttribute('last_active_label', $lastSeenByFriend->has($post->user_id) ? FriendPresence::label($lastSeenByFriend->get($post->user_id)) : null);
            $post->user->setAttribute('is_online_friend', $post->user->last_active_label === 'Active now');
            foreach ($post->comments as $comment) {
                $comment->user->setAttribute('last_active_label', $lastSeenByFriend->has($comment->user_id) ? FriendPresence::label($lastSeenByFriend->get($comment->user_id)) : null);
                $comment->user->setAttribute('is_online_friend', $comment->user->last_active_label === 'Active now');
                foreach ($comment->replies as $reply) {
                    $reply->user->setAttribute('last_active_label', $lastSeenByFriend->has($reply->user_id) ? FriendPresence::label($lastSeenByFriend->get($reply->user_id)) : null);
                    $reply->user->setAttribute('is_online_friend', $reply->user->last_active_label === 'Active now');
                }
            }
        }

        $followingIds = $user->following()
            ->pluck('users.id');

        $peopleToFollow = User::query()
            ->where('id', '!=', $user->id)
            ->where('is_permanently_banned', false)
            ->where(fn ($query) => $query->whereNull('banned_until')->orWhere('banned_until', '<=', now()))
            ->whereNotIn('id', $followingIds)
            ->latest()
            ->take(5)
            ->get();

        $myDayStories = MyDayStory::visibleTo($user)
            ->with('user')->withExists(['views as viewed_by_me' => fn ($q) => $q->where('viewer_id', $user->id)])
            ->latest()
            ->take(100)
            ->get()
            ->groupBy('user_id')
            ->map(fn ($stories) => $stories->first())
            ->values();

        return view('newsfeed', compact(
            'user',
            'posts',
            'peopleToFollow',
            'myDayStories'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'media' => [
                'nullable',
                'file',
                // The local PHP server has a 40 MB request limit. Leave room
                // for multipart fields so oversized requests reach validation.
                'max:35840',
                'mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov',
            ],
            'audience' => ['required', 'in:public,friends'],
        ], [
            'media.max' => 'Choose an image or video that is 35 MB or smaller.',
        ]);

        if (
            empty($validated['content']) &&
            !$request->hasFile('media')
        ) {
            return back()
                ->withErrors([
                    'content' => 'Please write something or upload a photo/video.'
                ])
                ->withInput();
        }

        $mediaPath = null;
        $mediaType = 'none';

        if ($request->hasFile('media')) {

            $file = $request->file('media');

            $mediaPath = $file->store(
                'posts',
                'public'
            );

            if (str_starts_with($file->getMimeType(), 'image/')) {
                $mediaType = 'image';
            } elseif (str_starts_with($file->getMimeType(), 'video/')) {
                $mediaType = 'video';
            }
        }

        Post::create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'] ?? null,
            'media_path' => $mediaPath,
            'media_type' => $mediaType,
            'audience' => $validated['audience'],
        ]);

        return redirect()
            ->route('newsfeed')
            ->with('success', 'Your post has been published.');
    }

    public function update(Request $request, Post $post)
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'content' => ['nullable', 'string', 'max:5000'],
            'media' => ['nullable', 'file', 'max:51200', 'mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov'],
            'audience' => ['required', 'in:public,friends'],
            'remove_media' => ['nullable', 'boolean'],
        ]);

        $keepsExistingMedia = $post->media_path && !$request->boolean('remove_media');
        if (empty($validated['content']) && !$request->hasFile('media') && !$keepsExistingMedia) {
            return back()->withErrors(['content' => 'A post must include text or media.']);
        }

        if ($request->boolean('remove_media') && $post->media_path) {
            Storage::disk('public')->delete($post->media_path);
            $post->media_path = null;
            $post->media_type = 'none';
        }

        if ($request->hasFile('media')) {
            if ($post->media_path) Storage::disk('public')->delete($post->media_path);
            $file = $request->file('media');
            $post->media_path = $file->store('posts', 'public');
            $post->media_type = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'video';
        }

        $post->content = $validated['content'] ?? null;
        $post->audience = $validated['audience'];
        $post->save();

        return back()->with('success', 'Your post has been updated.');
    }

    public function report(Request $request, Post $post)
    {
        abort_if($post->user_id === $request->user()->id, 403);
        abort_if($post->user->is_admin, 403, 'NEX staff accounts cannot be reported.');
        $validated = $request->validate([
            'reason' => ['required', 'in:spam,harassment,inappropriate,other'],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        $report = $post->reports()->create(array_merge($validated, [
            'user_id' => $request->user()->id,
            'post_author_id' => $post->user_id,
            'reported_content' => $post->content,
            'reported_media_path' => $post->media_path,
            'reported_media_type' => $post->media_type,
        ]));

        $this->notifyAdminsOfReport($report, $request->user(), $post->user, 'post');

        return back()->with('success', 'Thanks. Your report has been submitted.');
    }

    public function reportUser(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 403);
        abort_if($user->is_admin, 403, 'NEX staff accounts cannot be reported.');

        $validated = $request->validate([
            'reason' => ['required', 'in:spam,harassment,inappropriate,other'],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        $report = PostReport::create(array_merge($validated, [
            'post_id' => null,
            'user_id' => $request->user()->id,
            'post_author_id' => $user->id,
            'reported_content' => 'Profile report for @' . $user->username,
            'reported_media_type' => 'profile',
        ]));

        $this->notifyAdminsOfReport($report, $request->user(), $user, 'account');

        return back()->with('success', 'Thanks. Your account report has been submitted to NEX.');
    }

    private function notifyAdminsOfReport(PostReport $report, User $reporter, User $reportedUser, string $subject): void
    {
        User::query()->where('is_admin', true)
            ->where('id', '!=', $reporter->id)
            ->get()
            ->each(fn (User $admin) => $admin->notify(new NexActivityNotification(
                action: 'user_report',
                message: $reporter->name . ' submitted a new ' . $subject . ' report about ' . $reportedUser->name . '.',
                actorId: $reporter->id,
                actorName: $reporter->name,
                url: '/admin/reports?status=pending',
                context: [
                    'report_id' => $report->id,
                    'report_type' => $subject,
                    'reported_user_id' => $reportedUser->id,
                    'reported_user_name' => $reportedUser->name,
                ],
            )));
    }

    public function share(Request $request, Post $post)
    {
        abort_unless(Post::visibleTo($request->user())->whereKey($post->id)->exists(), 404);
        abort_if($post->user_id === $request->user()->id, 422, 'You cannot share your own post.');

        $share = PostShare::firstOrCreate([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
        ]);
        if ($share->wasRecentlyCreated) {
            $post->user->notify(new NexActivityNotification(
                action: 'share',
                message: $request->user()->name . ' shared your post.',
                actorId: $request->user()->id,
                actorName: $request->user()->name,
                url: '/newsfeed#post-' . $post->id,
                context: ['post_id' => $post->id],
            ));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'shared' => $share->wasRecentlyCreated,
                'shares_count' => $post->shares()->count(),
                'message' => $share->wasRecentlyCreated ? 'Post shared.' : 'You already shared this post.',
            ]);
        }

        return back()->with('success', $share->wasRecentlyCreated ? 'Post shared.' : 'You already shared this post.');
    }

    public function destroy(Request $request, Post $post)
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($post->media_path && !$post->reports()->exists()) {
            Storage::disk('public')->delete($post->media_path);
        }

        $post->delete();

        return back()->with(
            'success',
            'Post deleted.'
        );
    }
}
