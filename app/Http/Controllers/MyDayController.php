<?php

namespace App\Http\Controllers;

use App\Models\MyDayStory;
use App\Models\MyDayHighlight;
use App\Models\MyDayStoryView;
use App\Support\MyDayVideoDuration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class MyDayController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();
        $stories = MyDayStory::visibleTo($viewer)
            ->with('user')->withExists(['views as viewed_by_me' => fn ($q) => $q->where('viewer_id', $viewer->id)])
            ->latest()
            ->get();
        $storiesByUser = $stories->groupBy('user_id');

        return view('myday.index', [
            'storiesByUser' => $storiesByUser,
            'myStories' => $storiesByUser->get($viewer->id, collect()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'media' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:30000'],
            'caption' => ['nullable', 'string', 'max:500'],
            'music' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a,aac', 'max:8192'],
            'lyrics' => ['nullable', 'string', 'max:2000'],
            'audience' => ['nullable', 'in:friends,public'],
        ], [
            'media.mimes' => 'Use a JPG, PNG, WEBP image or an MP4, MOV, or WEBM video.',
            'media.max' => 'The photo or video must be 30 MB or smaller.',
            'music.mimes' => 'Background music must be an MP3, M4A, AAC, WAV, or OGG file.',
        ]);

        $file = $request->file('media');
        $mediaType = str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'image';

        if ($mediaType === 'video') {
            $duration = MyDayVideoDuration::seconds($file->getRealPath());
            if ($duration === null) {
                throw ValidationException::withMessages([
                    'media' => 'NEX could not read this video’s duration. Please upload a valid MP4 or MOV video.',
                ]);
            }

            if ($duration > 60) {
                throw ValidationException::withMessages([
                    'media' => 'My Day videos can be up to 1 minute long.',
                ]);
            }
        }

        // Stories may be friends-only, so keep their files off the public disk.
        $mediaPath = $file->store('myday/media', 'local');
        $musicPath = null;

        try {
            if ($request->hasFile('music')) {
                $musicPath = $request->file('music')->store('myday/music', 'local');
            }

            MyDayStory::create([
                'user_id' => $request->user()->id,
                'caption' => $validated['caption'] ?? null,
                'media_path' => $mediaPath,
                'media_type' => $mediaType,
                'music_path' => $musicPath,
                'lyrics' => $validated['lyrics'] ?? null,
                'audience' => $validated['audience'] ?? 'friends',
                'expires_at' => now()->addHours(24),
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(array_filter([$mediaPath, $musicPath]));
            throw $exception;
        }

        return redirect()->route('myday.index')->with('success', 'Your My Day is live for 24 hours.');
    }

    public function show(Request $request, MyDayStory $story): View
    {
        $viewer = $request->user();
        $story = MyDayStory::allowedTo($viewer)
            ->with('user')
            ->where(fn ($q) => $q->active()->orWhereHas('highlights'))
            ->whereKey($story->id)
            ->firstOrFail();

        if ($story->user_id !== $viewer->id) {
            MyDayStoryView::firstOrCreate(['story_id' => $story->id, 'viewer_id' => $viewer->id], ['viewed_at' => now()]);
        }

        $authorStories = MyDayStory::allowedTo($viewer)->where(fn ($q) => $q->active()->orWhereHas('highlights'))
            ->where('user_id', $story->user_id)
            ->oldest()
            ->get();
        $currentIndex = $authorStories->search(fn (MyDayStory $item) => $item->id === $story->id);
        $olderStory = $currentIndex !== false ? $authorStories->get($currentIndex - 1) : null;
        $newerStory = $currentIndex !== false ? $authorStories->get($currentIndex + 1) : null;

        $storyUsers = MyDayStory::visibleTo($viewer)->with('user')
            ->withExists(['views as viewed_by_me' => fn ($q) => $q->where('viewer_id', $viewer->id)])
            ->latest()->get()->groupBy('user_id')->map(fn ($items) => $items->first())->values();

        return view('myday.show', compact('story', 'olderStory', 'newerStory', 'storyUsers'));
    }

    public function media(Request $request, MyDayStory $story, string $kind)
    {
        $story = MyDayStory::allowedTo($request->user())->where(fn ($q) => $q->active()->orWhereHas('highlights'))->whereKey($story->id)->firstOrFail();
        abort_unless(in_array($kind, ['media', 'music'], true), 404);

        $path = $kind === 'music' ? $story->music_path : $story->media_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, MyDayStory $story): RedirectResponse
    {
        abort_unless($story->user_id === $request->user()->id, 403);

        Storage::disk('local')->delete(array_filter([$story->media_path, $story->music_path]));
        $story->delete();

        return redirect()->route('myday.index')->with('success', 'Your My Day was deleted.');
    }

    public function feature(Request $request): RedirectResponse
    {
        $data = $request->validate(['story_id' => ['required', 'integer'], 'title' => ['required', 'string', 'max:50'], 'description' => ['nullable', 'string', 'max:160']]);
        $story = $request->user()->myDayStories()->whereKey($data['story_id'])->active()->firstOrFail();
        $highlight = MyDayHighlight::create(['user_id' => $request->user()->id, 'title' => $data['title'], 'description' => $data['description'] ?? null]);
        $highlight->stories()->attach($story->id);
        return back()->with('success', 'Story added to your profile highlights.');
    }

    public function addToFeature(Request $request, MyDayHighlight $highlight): RedirectResponse
    {
        abort_unless($highlight->user_id === $request->user()->id, 403);
        $data = $request->validate(['story_id' => ['required', 'integer']]);
        $story = $request->user()->myDayStories()->whereKey($data['story_id'])->active()->firstOrFail();
        $highlight->stories()->syncWithoutDetaching([$story->id]);
        return back()->with('success', 'Story added to the selected highlight.');
    }

    public function deleteHighlight(Request $request, MyDayHighlight $highlight): RedirectResponse
    {
        abort_unless($highlight->user_id === $request->user()->id, 403);
        $highlight->delete();
        return back()->with('success', 'Profile highlight removed.');
    }
}
