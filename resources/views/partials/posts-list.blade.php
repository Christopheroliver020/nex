@forelse($posts as $post)
                    @php($viewerReacted = $post->reactions->contains('user_id', Auth::id()))

                    <article class="nex-post-card" id="post-{{ $post->id }}">

                        {{-- =========================================
                            POST HEADER
                        ========================================== --}}

                        <div class="nex-post-header">

                            <a href="{{ route('profiles.show', $post->user) }}" class="nex-avatar nex-avatar-link" aria-label="View {{ $post->user->name }}'s profile">
                                @if ($post->user->profile_photo)
                                    <img src="{{ asset('storage/' . $post->user->profile_photo) }}" alt="">
                                @else
                                    {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                @endif
                                @if($post->user->is_online_friend ?? false)<span class="nex-active-indicator" role="img" aria-label="Active now" title="Active now"></span>@endif
                            </a>


                            <div class="nex-post-author">

                                <a href="{{ route('profiles.show', $post->user) }}" class="nex-post-author-link"><strong>{{ $post->user->name }}</strong></a>

                               <span>
                                    {{ '@' . strtolower(str_replace(' ', '', $post->user->name)) }}
                                    ·
                                    {{ $post->created_at->diffForHumans() }}
                                    @if($post->audience === 'friends')
                                        · Friends only
                                    @endif
                                    @if($post->user->last_active_label ?? false)
                                        · <span class="nex-presence-copy {{ $post->user->is_online_friend ? 'is-online' : '' }}">{{ $post->user->last_active_label }}</span>
                                    @endif
                                </span>

                            </div>


                            {{-- POST OPTIONS / DELETE --}}
                            @if($post->user_id === Auth::id())
                                <details class="nex-post-menu">
                                    <summary class="nex-post-more" aria-label="Post options">@include('partials.icon', ['icon' => 'more'])</summary>
                                    <div class="nex-post-menu-panel">
                                        <details class="nex-post-edit">
                                            <summary>Edit post</summary>
                                            <form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data">
                                                @csrf
                                                @method('PATCH')
                                                <label>Post text<textarea name="content" maxlength="5000">{{ $post->content }}</textarea></label>
                                                <label>Audience<select name="audience"><option value="public" @selected($post->audience === 'public')>Public</option><option value="friends" @selected($post->audience === 'friends')>Friends only</option></select></label>
                                                @if($post->media_path)
                                                    <label class="nex-post-remove-media"><input type="checkbox" name="remove_media" value="1"> Remove current photo or video</label>
                                                @else
                                                    <label>Add photo or video<input type="file" name="media" accept="image/*,video/*"></label>
                                                @endif
                                                <button type="submit" class="nex-post-menu-submit">Save changes</button>
                                            </form>
                                        </details>
                                        <form method="POST" action="{{ route('posts.destroy', $post) }}" data-post-delete-form>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="nex-post-menu-danger">Delete post</button>
                                        </form>
                                    </div>
                                </details>
                            @elseif(!$post->user->is_admin)
                                <details class="nex-post-menu">
                                    <summary class="nex-post-more" aria-label="Post options">@include('partials.icon', ['icon' => 'more'])</summary>
                                    <div class="nex-post-menu-panel">
                                        <form method="POST" action="{{ route('posts.report', $post) }}">
                                            @csrf
                                            <strong>Report post</strong>
                                            <label>Reason<select name="reason" required><option value="">Choose a reason</option><option value="spam">Spam</option><option value="harassment">Harassment</option><option value="inappropriate">Inappropriate content</option><option value="other">Other</option></select></label>
                                            <textarea name="details" maxlength="1000" placeholder="Add details (optional)"></textarea>
                                            <button type="submit" class="nex-post-menu-submit">Send report</button>
                                        </form>
                                    </div>
                                </details>
                            @endif

                        </div>

                        {{-- =========================================
                            POST CONTENT
                        ========================================== --}}

                        @if($post->content)

                            <div class="nex-post-content">

                                <p>
                                    {!! nl2br(e($post->content)) !!}
                                </p>

                            </div>

                        @endif


                        {{-- =========================================
                            POST MEDIA
                        ========================================== --}}

                        @if(
                            $post->media_type === 'image'
                            && $post->media_path
                        )

                            <div class="nex-post-media-wrapper">
                                <button type="button" class="nex-post-image-open" data-image-viewer aria-label="View full image">
                                    <img
                                        src="{{ asset('storage/' . $post->media_path) }}"
                                        alt="Post image"
                                        class="nex-post-media"
                                    >
                                </button>
                            </div>

                        @endif


                        @if(
                            $post->media_type === 'video'
                            && $post->media_path
                        )

                            <div class="nex-post-media-wrapper nex-feed-video-player is-muted">

                                <video
                                    class="nex-post-media"
                                    data-feed-video
                                    playsinline
                                    loop
                                    preload="metadata"
                                    aria-label="Video by {{ $post->user->name }}"
                                >

                                    <source
                                        src="{{ asset('storage/' . $post->media_path) }}"
                                    >

                                </video>

                                <button class="nex-feed-video-play" type="button" data-feed-video-toggle aria-label="Pause video">
                                    <span class="feed-video-play-icon">@include('partials.icon', ['icon' => 'play'])</span>
                                    <span class="feed-video-pause-icon">@include('partials.icon', ['icon' => 'pause'])</span>
                                </button>
                                <div class="nex-feed-video-controls">
                                    <button type="button" class="nex-feed-video-sound" data-feed-video-sound aria-label="Unmute video" aria-pressed="false">
                                        <span class="feed-video-muted-icon">@include('partials.icon', ['icon' => 'volume-off'])</span>
                                        <span class="feed-video-unmuted-icon">@include('partials.icon', ['icon' => 'volume'])</span>
                                    </button>
                                    <time data-feed-video-current>0:00</time>
                                    <input type="range" min="0" max="100" value="0" step="0.1" data-feed-video-seek aria-label="Seek video">
                                    <time data-feed-video-duration>0:00</time>
                                </div>

                            </div>

                        @endif


                        {{-- =========================================
                            REACTION SUMMARY
                        ========================================== --}}

                        <div class="nex-reaction-summary">

                            <div class="nex-reaction-icons">

                                <span class="nex-reaction-icon love" @if($post->reactions_count < 1) hidden @endif>@include('partials.icon', ['icon' => 'heart'])</span>

                            </div>


                            <div class="nex-reaction-summary-text">

                                <span class="nex-reaction-count" @if($post->reactions_count < 1) hidden @endif>{{ $post->reactions_count }}</span>

                                @if($post->comments_count > 0)

                                    <button
                                        type="button"
                                        onclick="toggleComments({{ $post->id }})"
                                    >
                                        {{ $post->comments_count }}
                                        {{ $post->comments_count == 1 ? 'comment' : 'comments' }}
                                    </button>

                                @endif

                                @if($post->shares_count > 0)
                                    <span class="nex-share-count-summary">{{ $post->shares_count }} {{ $post->shares_count === 1 ? 'share' : 'shares' }}</span>
                                @endif

                            </div>

                        </div>


                        {{-- =========================================
                            POST ACTION BUTTONS
                        ========================================== --}}

                        <div class="nex-post-actions">

                            {{-- LIKE --}}

                            <form
                                method="POST"
                                action="{{ route('posts.react', $post) }}"
                                class="nex-post-action-form nex-react-form"
                            >

                                @csrf

                                <button
                                    type="submit"
                                    class="nex-facebook-action nex-react-button {{ $viewerReacted ? 'is-reacted' : '' }}"
                                    aria-pressed="{{ $viewerReacted ? 'true' : 'false' }}"
                                    aria-label="{{ $viewerReacted ? 'Unlike this post' : 'Like this post' }}"
                                >

                                    <span class="nex-action-icon">@include('partials.icon', ['icon' => 'heart'])</span>

                                    <span>
                                        Like
                                    </span>

                                </button>

                            </form>


                            {{-- COMMENT --}}

                            <button
                                type="button"
                                class="nex-facebook-action"
                                onclick="toggleComments({{ $post->id }})"
                            >

                                <span class="nex-action-icon">@include('partials.icon', ['icon' => 'comment'])</span>

                                <span>
                                    Comment
                                </span>

                            </button>


                            {{-- SHARE --}}

                            <form method="POST" action="{{ route('posts.share', $post) }}" class="nex-post-action-form nex-share-form" data-share-url="{{ url('/newsfeed') }}#post-{{ $post->id }}" data-share-title="{{ $post->user->name }} on NEX">
                                @csrf
                                <button type="submit" class="nex-facebook-action nex-share-button" aria-label="Share this post" @disabled($post->user_id === Auth::id())>

                                <span class="nex-action-icon">@include('partials.icon', ['icon' => 'share'])</span>

                                <span>
                                    Share
                                </span>

                                </button>
                            </form>

                        </div>


                        {{-- =========================================
                            COMMENTS SECTION
                        ========================================== --}}

                        <div
                            id="comments-{{ $post->id }}"
                            class="nex-comments"
                        >

                            {{-- =====================================
                                WRITE COMMENT
                            ====================================== --}}

                            <form
                                method="POST"
                                action="{{ route('comments.store', $post) }}"
                                class="nex-comment-form"
                            >

                                @csrf

                                <div class="nex-avatar nex-comment-avatar">
                                    @if(Auth::user()->profile_photo)
                                        <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}">
                                    @else
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    @endif
                                </div>


                                <div class="nex-comment-input-wrapper">

                                    <input
                                        type="text"
                                        name="content"
                                        placeholder="Write a comment..."
                                        required
                                    >

                                    <button
                                        type="submit"
                                        aria-label="Send comment"
                                    >
                                        @include('partials.icon', ['icon' => 'send'])
                                    </button>

                                </div>

                            </form>


                            {{-- =====================================
                                COMMENTS
                            ====================================== --}}

                            <div class="nex-comments-list">

                                @foreach($post->comments as $comment)

                                    <div class="nex-comment">

                                        <div class="nex-avatar nex-small-avatar">
                                            @if($comment->user->profile_photo)
                                                <img src="{{ asset('storage/' . $comment->user->profile_photo) }}" alt="{{ $comment->user->name }}">
                                            @else
                                                {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                            @endif
                                    @if($comment->user->is_online_friend ?? false)<span class="nex-active-indicator" role="img" aria-label="Active now" title="Active now"></span>@endif
                                        </div>


                                        <div class="nex-comment-body">

                                            {{-- COMMENT BUBBLE --}}

                                            <div class="nex-comment-box">

                                                <strong>
                                                    {{ $comment->user->name }}
                                                </strong>

                                                <p>
                                                    {!! nl2br(
                                                        e($comment->content)
                                                    ) !!}
                                                </p>

                                            </div>


                                            {{-- COMMENT ACTIONS --}}

                                            <div class="nex-comment-actions">

                                                {{-- LIKE COMMENT --}}

                                                <form
                                                    method="POST"
                                                    action="{{ route(
                                                        'comments.react',
                                                        $comment
                                                    ) }}"
                                                >

                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        class="nex-comment-action"
                                                    >
                                                        @include('partials.icon', ['icon' => 'heart']) Like

                                                        @if($comment->reactions->count() > 0)
                                                            · {{ $comment->reactions->count() }}
                                                        @endif
                                                    </button>

                                                </form>


                                                {{-- REPLY --}}

                                                <button
                                                    type="button"
                                                    class="nex-comment-action"
                                                    onclick="toggleReply({{ $comment->id }})"
                                                >
                                                    @include('partials.icon', ['icon' => 'comment']) Reply
                                                </button>


                                                <span class="nex-comment-time">
                                                    {{ $comment->created_at->diffForHumans() }}
                                                </span>


                                                {{-- DELETE COMMENT --}}

                                                @if($comment->user_id === Auth::id())

                                                    <form
                                                        method="POST"
                                                        action="{{ route(
                                                            'comments.destroy',
                                                            $comment
                                                        ) }}"
                                                        class="nex-delete-comment-form"
                                                    >

                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="nex-comment-action nex-delete-action"
                                                        >
                                                            Delete
                                                        </button>

                                                    </form>

                                                @endif

                                            </div>


                                            {{-- =================================
                                                REPLY FORM
                                            ================================== --}}

                                            <form
                                                id="reply-{{ $comment->id }}"
                                                method="POST"
                                                action="{{ route(
                                                    'comments.store',
                                                    $post
                                                ) }}"
                                                class="nex-reply-form"
                                                style="display:none;"
                                            >

                                                @csrf

                                                <input
                                                    type="hidden"
                                                    name="parent_id"
                                                    value="{{ $comment->id }}"
                                                >

                                                <div class="nex-avatar nex-reply-avatar">
                                                    @if(Auth::user()->profile_photo)
                                                        <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}">
                                                    @else
                                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                                    @endif
                                                </div>

                                                <div class="nex-comment-input-wrapper">

                                                    <input
                                                        type="text"
                                                        name="content"
                                                        placeholder="Write a reply..."
                                                        required
                                                    >

                                                    <button type="submit">
                                                        @include('partials.icon', ['icon' => 'send'])
                                                    </button>

                                                </div>

                                            </form>


                                            {{-- =================================
                                                REPLIES
                                            ================================== --}}

                                            @foreach($comment->replies as $reply)

                                                <div class="nex-reply">

                                                    <div class="nex-avatar nex-small-avatar">
                                                        @if($reply->user->profile_photo)
                                                            <img src="{{ asset('storage/' . $reply->user->profile_photo) }}" alt="{{ $reply->user->name }}">
                                                        @else
                                                            {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                                                        @endif
                                                    @if($reply->user->is_online_friend ?? false)<span class="nex-active-indicator" role="img" aria-label="Active now" title="Active now"></span>@endif
                                                    </div>


                                                    <div class="nex-comment-body">

                                                        <div class="nex-comment-box">

                                                            <strong>
                                                                {{ $reply->user->name }}
                                                            </strong>

                                                            <p>
                                                                {!! nl2br(
                                                                    e($reply->content)
                                                                ) !!}
                                                            </p>

                                                        </div>


                                                        <div class="nex-comment-actions">

                                                            {{-- REPLY LIKE --}}

                                                            <form
                                                                method="POST"
                                                                action="{{ route(
                                                                    'comments.react',
                                                                    $reply
                                                                ) }}"
                                                            >

                                                                @csrf

                                                                <button
                                                                    type="submit"
                                                                    class="nex-comment-action"
                                                                >

                                                                    @include('partials.icon', ['icon' => 'heart']) Like

                                                                    @if($reply->reactions->count() > 0)

                                                                        · {{ $reply->reactions->count() }}

                                                                    @endif

                                                                </button>

                                                            </form>


                                                            <span class="nex-comment-time">

                                                                {{ $reply->created_at->diffForHumans() }}

                                                            </span>


                                                            {{-- DELETE REPLY --}}

                                                            @if($reply->user_id === Auth::id())

                                                                <form
                                                                    method="POST"
                                                                    action="{{ route(
                                                                        'comments.destroy',
                                                                        $reply
                                                                    ) }}"
                                                                >

                                                                    @csrf
                                                                    @method('DELETE')

                                                                    <button
                                                                        type="submit"
                                                                        class="nex-comment-action nex-delete-action"
                                                                    >
                                                                        Delete
                                                                    </button>

                                                                </form>

                                                            @endif

                                                        </div>

                                                    </div>

                                                </div>

                                            @endforeach

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        </div>

                    </article>

                @empty

                    <div class="nex-empty-feed">

                        <div>
                            @include('partials.icon', ['icon' => 'sparkles'])
                        </div>

                        <h3>{{ $emptyTitle ?? 'No posts yet' }}</h3>

                        <p>{{ $emptyMessage ?? 'Be the first person to share something on NEX.' }}</p>

                    </div>

                @endforelse
