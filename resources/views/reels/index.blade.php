<x-app-layout>
    <div class="nex-app-body nex-reels-page">
        <main class="nex-reels-main">
            <section class="nex-reels-list" aria-label="Video reels">
                @forelse($posts as $post)
                    @php($viewerReacted = $post->reactions->contains('user_id', Auth::id()))
                    <article class="nex-reel-card" id="reel-{{ $post->id }}">
                        <div class="nex-reel-stage">
                            <video class="nex-reel-video" data-reel-video src="{{ asset('storage/' . $post->media_path) }}" playsinline loop preload="metadata" aria-label="Video by {{ $post->user->name }}"></video>
                            <button class="nex-reel-play-toggle" type="button" data-reel-toggle aria-label="Play video"><span class="reel-play-icon">@include('partials.icon', ['icon' => 'play'])</span><span class="reel-pause-icon">@include('partials.icon', ['icon' => 'pause'])</span></button>
                            <div class="nex-reel-shade"></div>

                            <div class="nex-reel-topline">
                                <span class="nex-reel-tag">@include('partials.icon', ['icon' => 'reels']) <span>REEL</span></span>
                                @if($post->user_id === Auth::id())
                                    <details class="nex-reel-menu"><summary aria-label="Post options">@include('partials.icon', ['icon' => 'more'])</summary><div class="nex-reel-menu-panel">
                                        <details><summary>Edit reel</summary><form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data">@csrf @method('PATCH')<label>Caption<textarea name="content" maxlength="5000">{{ $post->content }}</textarea></label><label>Audience<select name="audience"><option value="public" @selected($post->audience === 'public')>Public</option><option value="friends" @selected($post->audience === 'friends')>Friends only</option></select></label><button type="submit">Save changes</button></form></details>
                                        <form method="POST" action="{{ route('posts.destroy', $post) }}" data-confirm-delete data-confirm-title="Delete this reel?" data-confirm-message="This reel will be permanently removed. You cannot undo this action." data-confirm-action="Delete reel">@csrf @method('DELETE')<button class="nex-reel-delete" type="submit">Delete reel</button></form>
                                    </div></details>
                                @elseif(!$post->user->is_admin)
                                    <details class="nex-reel-menu"><summary aria-label="Post options">@include('partials.icon', ['icon' => 'more'])</summary><div class="nex-reel-menu-panel"><form method="POST" action="{{ route('posts.report', $post) }}">@csrf<strong>Report reel</strong><label>Reason<select name="reason" required><option value="">Choose a reason</option><option value="spam">Spam</option><option value="harassment">Harassment</option><option value="inappropriate">Inappropriate content</option><option value="other">Other</option></select></label><textarea name="details" maxlength="1000" placeholder="Add details (optional)"></textarea><button type="submit">Send report</button></form></div></details>
                                @endif
                            </div>

                            <div class="nex-reel-overlay">
                                <div class="nex-reel-author-row">
                                    <a class="nex-reel-avatar" href="{{ route('profiles.show', $post->user) }}" aria-label="View {{ $post->user->name }}’s profile">
                                        @if($post->user->profile_photo)<img src="{{ asset('storage/' . $post->user->profile_photo) }}" alt="">@else{{ strtoupper(substr($post->user->name, 0, 1)) }}@endif
                                    </a>
                                    <a class="nex-reel-author" href="{{ route('profiles.show', $post->user) }}">{{ $post->user->name }}</a>
                                    @if($post->user_id !== Auth::id())
                                        @if(in_array($post->user_id, $followingIds, true))
                                            <form method="POST" action="{{ route('users.unfollow', $post->user) }}">@csrf @method('DELETE')<button class="nex-reel-follow is-following" type="submit">Following</button></form>
                                        @else
                                            <form method="POST" action="{{ route('users.follow', $post->user) }}">@csrf<button class="nex-reel-follow" type="submit">Follow</button></form>
                                        @endif
                                    @endif
                                </div>
                                @if($post->content)<p class="nex-reel-caption">{{ $post->content }}</p>@endif
                                <span class="nex-reel-time">{{ $post->created_at->diffForHumans() }}@if($post->audience === 'friends') · Friends only @endif</span>
                            </div>

                            <button class="nex-reel-sound-toggle" type="button" data-reel-sound aria-label="Unmute video"><span class="reel-muted-icon">@include('partials.icon', ['icon' => 'volume-off'])</span><span class="reel-unmuted-icon">@include('partials.icon', ['icon' => 'volume'])</span></button>
                        </div>

                        <aside class="nex-reel-actions" aria-label="Reel actions">
                            <form method="POST" action="{{ route('posts.react', $post) }}" class="nex-react-form">@csrf
                                <button type="submit" class="nex-reel-action nex-react-button {{ $viewerReacted ? 'is-reacted' : '' }}" aria-pressed="{{ $viewerReacted ? 'true' : 'false' }}" aria-label="{{ $viewerReacted ? 'Unlike this reel' : 'Like this reel' }}"><span class="nex-reel-action-icon">@include('partials.icon', ['icon' => 'heart'])</span><span class="nex-reaction-count" @if($post->reactions_count < 1) hidden @endif>{{ $post->reactions_count }}</span></button>
                            </form>
                            <button type="button" class="nex-reel-action" onclick="toggleComments({{ $post->id }})" aria-label="Open comments"><span class="nex-reel-action-icon">@include('partials.icon', ['icon' => 'comment'])</span><span>{{ $post->comments_count }}</span></button>
                            <form method="POST" action="{{ route('posts.share', $post) }}" class="nex-share-form" data-share-url="{{ url('/newsfeed') }}#post-{{ $post->id }}" data-share-title="{{ $post->user->name }}’s reel on NEX">@csrf<button type="submit" class="nex-reel-action nex-share-button" @disabled($post->user_id === Auth::id()) aria-label="Share reel"><span class="nex-reel-action-icon">@include('partials.icon', ['icon' => 'share'])</span><span class="nex-share-count">{{ $post->shares_count }}</span></button></form>
                            <button type="button" class="nex-reel-action nex-reel-sound-rail is-muted" data-reel-sound aria-label="Unmute video"><span class="nex-reel-action-icon"><span class="reel-muted-icon">@include('partials.icon', ['icon' => 'volume-off'])</span><span class="reel-unmuted-icon">@include('partials.icon', ['icon' => 'volume'])</span></span></button>
                        </aside>

                        <section id="comments-{{ $post->id }}" class="nex-reel-comments" style="display:none" aria-label="Comments on reel">
                            <button type="button" class="nex-reel-comments-backdrop" onclick="toggleComments({{ $post->id }})" aria-label="Close comments"></button>
                            <div class="nex-reel-comments-panel"><header><div><span>COMMENTS</span><strong>{{ $post->comments_count }} {{ $post->comments_count === 1 ? 'comment' : 'comments' }}</strong></div><button type="button" onclick="toggleComments({{ $post->id }})" aria-label="Close comments">×</button></header>
                                <div class="nex-reel-comments-list">
                                    @forelse($post->comments as $comment)
                                        @php($commentReacted = $comment->reactions->contains('user_id', Auth::id()))
                                        <article class="nex-reel-comment">
                                            <a class="nex-reel-comment-avatar" href="{{ route('profiles.show', $comment->user) }}">@if($comment->user->profile_photo)<img src="{{ asset('storage/' . $comment->user->profile_photo) }}" alt="">@else{{ strtoupper(substr($comment->user->name, 0, 1)) }}@endif</a>
                                            <div class="nex-reel-comment-content">
                                                <strong>{{ $comment->user->name }}</strong><p>{{ $comment->content }}</p><small>{{ $comment->created_at->diffForHumans() }}</small>
                                                <div class="nex-reel-comment-actions">
                                                    <form method="POST" action="{{ route('comments.react', $comment) }}" class="nex-reel-comment-react">@csrf<button type="submit" class="{{ $commentReacted ? 'is-reacted' : '' }}" aria-pressed="{{ $commentReacted ? 'true' : 'false' }}"><span>@include('partials.icon', ['icon' => 'heart'])</span><span class="nex-reel-comment-reaction-label">{{ $commentReacted ? 'Liked' : 'Like' }}</span><span class="nex-reel-comment-reaction-count" @if($comment->reactions->isEmpty()) hidden @endif>{{ $comment->reactions->count() }}</span></button></form>
                                                    <button type="button" class="nex-reel-reply-toggle" onclick="toggleReply({{ $comment->id }})">Reply</button>
                                                </div>
                                                <form id="reply-{{ $comment->id }}" class="nex-reel-reply-form" method="POST" action="{{ route('comments.store', $post) }}" style="display:none">@csrf<input type="hidden" name="parent_id" value="{{ $comment->id }}"><input name="content" maxlength="2000" required placeholder="Write a reply…"><button type="submit" aria-label="Send reply">@include('partials.icon', ['icon' => 'send'])</button></form>
                                                @foreach($comment->replies as $reply)
                                                    @php($replyReacted = $reply->reactions->contains('user_id', Auth::id()))
                                                    <div class="nex-reel-reply">
                                                        <a class="nex-reel-comment-avatar" href="{{ route('profiles.show', $reply->user) }}">@if($reply->user->profile_photo)<img src="{{ asset('storage/' . $reply->user->profile_photo) }}" alt="">@else{{ strtoupper(substr($reply->user->name, 0, 1)) }}@endif</a>
                                                        <div class="nex-reel-comment-content"><strong>{{ $reply->user->name }}</strong><p>{{ $reply->content }}</p><small>{{ $reply->created_at->diffForHumans() }}</small>
                                                            <div class="nex-reel-comment-actions"><form method="POST" action="{{ route('comments.react', $reply) }}" class="nex-reel-comment-react">@csrf<button type="submit" class="{{ $replyReacted ? 'is-reacted' : '' }}" aria-pressed="{{ $replyReacted ? 'true' : 'false' }}"><span>@include('partials.icon', ['icon' => 'heart'])</span><span class="nex-reel-comment-reaction-label">{{ $replyReacted ? 'Liked' : 'Like' }}</span><span class="nex-reel-comment-reaction-count" @if($reply->reactions->isEmpty()) hidden @endif>{{ $reply->reactions->count() }}</span></button></form></div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </article>
                                    @empty
                                        <div class="nex-reel-comments-empty">Be the first to comment.</div>
                                    @endforelse
                                </div>
                                <form class="nex-reel-comment-form" method="POST" action="{{ route('comments.store', $post) }}">@csrf
                                    <span class="nex-reel-comment-avatar">@if(Auth::user()->profile_photo)<img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="">@else{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}@endif</span><input name="content" maxlength="2000" required placeholder="Add a comment…"><button type="submit" aria-label="Send comment">@include('partials.icon', ['icon' => 'send'])</button>
                                </form>
                            </div>
                        </section>
                    </article>
                @empty
                    <div class="nex-reels-empty"><span>@include('partials.icon', ['icon' => 'video'])</span><h2>No reels yet</h2><p>Video posts from your community will show up here.</p><a href="{{ route('newsfeed') }}#create-post">Share a video</a></div>
                @endforelse
                @if($posts->hasPages())<div class="nex-reels-pagination">{{ $posts->links() }}</div>@endif
            </section>
        </main>
    </div>
</x-app-layout>
