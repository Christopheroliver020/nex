<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Notifications\NexActivityNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(User $user): View
    {
        abort_if($user->isAccountBanned(), 404);

        $viewer = request()->user();
        $user->load(['myDayHighlights.stories' => fn ($query) => $query->allowedTo($viewer)]);
        $user->setRelation('myDayHighlights', $user->myDayHighlights->filter(fn ($highlight) => $highlight->stories->isNotEmpty())->values());
        $user->loadCount(['followers', 'following']);
        $profileMedia = $this->profileMedia($user, $viewer);
        $posts = Post::visibleTo($viewer)->with(['user', 'reactions', 'comments.user', 'comments.reactions', 'comments.replies.user', 'comments.replies.reactions'])
            ->withCount(['reactions', 'comments', 'shares'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();
        $user->setAttribute('posts_count', $posts->count());
        $isFollowing = $viewer->following()->where('users.id', $user->id)->exists();

        return view('profile.show', compact('user', 'posts', 'isFollowing', 'profileMedia'));
    }

    public function media(User $user, string $type): View
    {
        abort_if($user->isAccountBanned(), 404);
        abort_unless(in_array($type, ['profile-pictures', 'photos', 'videos'], true), 404);

        $media = $this->profileMedia($user, request()->user())[$type];
        $titles = [
            'profile-pictures' => 'Profile pictures',
            'photos' => 'Photos',
            'videos' => 'Videos',
        ];

        return view('profile.media', [
            'user' => $user,
            'mediaItems' => $media,
            'mediaTitle' => $titles[$type],
        ]);
    }

    private function profileMedia(User $user, User $viewer): array
    {
        $updates = Post::query()
            ->where('user_id', $user->id)
            ->where('media_type', 'image')
            ->where('media_path', 'like', 'posts/profile-updates/%')
            ->latest()
            ->get(['id', 'content', 'media_path', 'created_at']);

        $profilePictures = $updates
            ->filter(fn (Post $post) => Str::contains(Str::lower($post->content), 'profile picture'))
            ->map(fn (Post $post) => ['path' => $post->media_path, 'label' => 'Profile picture', 'media_type' => 'image', 'created_at' => $post->created_at]);
        $photos = $updates
            ->filter(fn (Post $post) => Str::contains(Str::lower($post->content), 'cover photo'))
            ->map(fn (Post $post) => ['path' => $post->media_path, 'label' => 'Cover photo', 'media_type' => 'image', 'created_at' => $post->created_at]);

        if ($user->profile_photo && $profilePictures->isEmpty()) {
            $profilePictures->push(['path' => $user->profile_photo, 'label' => 'Profile picture', 'media_type' => 'image', 'created_at' => $user->updated_at]);
        }
        if ($user->cover_photo && $photos->isEmpty()) {
            $photos->push(['path' => $user->cover_photo, 'label' => 'Cover photo', 'media_type' => 'image', 'created_at' => $user->updated_at]);
        }

        $posts = Post::visibleTo($viewer)
            ->where('user_id', $user->id)
            ->whereNotNull('media_path')
            ->whereIn('media_type', ['image', 'video'])
            ->latest()
            ->get(['id', 'content', 'media_path', 'media_type', 'created_at']);

        $photos = $photos->concat($posts
            ->filter(fn (Post $post) => $post->media_type === 'image' && !str_starts_with($post->media_path, 'posts/profile-updates/'))
            ->map(fn (Post $post) => ['path' => $post->media_path, 'label' => 'Photo', 'media_type' => 'image', 'created_at' => $post->created_at]));
        $videos = $posts
            ->filter(fn (Post $post) => $post->media_type === 'video')
            ->map(fn (Post $post) => ['path' => $post->media_path, 'label' => 'Video', 'media_type' => 'video', 'created_at' => $post->created_at]);

        return [
            'profile-pictures' => $profilePictures->sortByDesc('created_at')->values(),
            'photos' => $photos->sortByDesc('created_at')->values(),
            'videos' => $videos->sortByDesc('created_at')->values(),
        ];
    }

    /**
     * Show the user's profile settings.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $postCount = $user->posts()->count();

        $followersCount = $user->followers()->count();

        $followingCount = $user->following()->count();

        return view('profile.edit', compact(
            'user',
            'postCount',
            'followersCount',
            'followingCount'
        ));
    }


    /**
     * Update profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'cover_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Profile Photo
        |--------------------------------------------------------------------------
        */

        $imageUpdates = [];

        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
            $imageUpdates[] = [
                'field' => 'profile_photo',
                'path' => $validated['profile_photo'],
                'caption' => 'updated their profile picture.',
                'action' => 'profile_photo_updated',
                'notification' => 'updated their profile picture.',
            ];
        }

        if ($request->hasFile('cover_photo')) {
            $validated['cover_photo'] = $request->file('cover_photo')->store('profile-covers', 'public');
            $imageUpdates[] = [
                'field' => 'cover_photo',
                'path' => $validated['cover_photo'],
                'caption' => 'updated their cover photo.',
                'action' => 'cover_photo_updated',
                'notification' => 'updated their cover photo.',
            ];
        }

        if (!isset($validated['profile_photo']) && !isset($validated['cover_photo'])) {
            return back()->withErrors(['profile_photo' => 'Choose a profile picture or cover photo before saving.']);
        }


        /*
        |--------------------------------------------------------------------------
        | Update User
        |--------------------------------------------------------------------------
        */

        $user->update($validated);

        // Keep the previous image files in storage and save each new image as a
        // Friends-only post so mutual followers can see the update in their feed.
        $mutualFriends = collect();
        if ($imageUpdates !== []) {
            $followingIds = $user->following()->pluck('users.id');
            $mutualFriends = $user->followers()->whereIn('users.id', $followingIds)->get();
        }

        foreach ($imageUpdates as $imageUpdate) {
            $historyPath = 'posts/profile-updates/' . Str::uuid() . '.' . pathinfo($imageUpdate['path'], PATHINFO_EXTENSION);
            Storage::disk('public')->copy($imageUpdate['path'], $historyPath);

            $post = Post::create([
                'user_id' => $user->id,
                'content' => $user->name . ' ' . $imageUpdate['caption'],
                'media_path' => $historyPath,
                'media_type' => 'image',
                'audience' => 'friends',
            ]);

            $url = '/newsfeed#post-' . $post->id;
            foreach ($mutualFriends as $friend) {
                $friend->notify(new NexActivityNotification(
                    action: $imageUpdate['action'],
                    message: $user->name . ' ' . $imageUpdate['notification'],
                    actorId: $user->id,
                    actorName: $user->name,
                    url: $url,
                    context: [
                        'post_id' => $post->id,
                        'image_type' => $imageUpdate['field'],
                    ],
                ));
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Email Verification
        |--------------------------------------------------------------------------
        */

        if ($user->wasChanged('email')) {

            $user->email_verified_at = null;

            $user->save();
        }


        return back()->with('status', 'profile-photo-updated');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:160'],
            'status' => ['nullable', 'string', 'max:100'],
        ], [
            'username.required' => 'Username is required.',
        ]);

        $user->update($validated);
        if ($user->wasChanged('email')) {
            $user->email_verified_at = null;
            $user->save();
        }

        return back()->with('status', 'account-details-updated');
    }


    /**
     * Delete account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => [
                'required',
                'current_password',
            ],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return RedirectResponse::to('/');
    }
}
