<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MyDayStory extends Model
{
    protected $fillable = [
        'user_id',
        'caption',
        'media_path',
        'media_type',
        'music_path',
        'lyrics',
        'audience',
        'expires_at',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function views(): HasMany { return $this->hasMany(MyDayStoryView::class, 'story_id'); }
    public function highlights(): BelongsToMany { return $this->belongsToMany(MyDayHighlight::class, 'my_day_highlight_stories', 'story_id', 'highlight_id')->withTimestamps(); }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        return $query->allowedTo($viewer)->active();
    }

    public function scopeAllowedTo(Builder $query, User $viewer): Builder
    {
        return $query
            ->whereHas('user', fn (Builder $author) => $author
                ->where('is_permanently_banned', false)
                ->where(fn (Builder $active) => $active->whereNull('banned_until')->orWhere('banned_until', '<=', now())))
            ->where(function (Builder $visible) use ($viewer) {
                $visible->where('user_id', $viewer->id)
                    ->orWhere('audience', 'public')
                    ->orWhere(function (Builder $friends) use ($viewer) {
                        $friends->where('audience', 'friends')
                            ->whereExists(function ($follows) use ($viewer) {
                                $follows->selectRaw('1')
                                    ->from('follows as viewer_follows_story_author')
                                    ->whereColumn('viewer_follows_story_author.following_id', 'my_day_stories.user_id')
                                    ->where('viewer_follows_story_author.follower_id', $viewer->id);
                            })
                            ->whereExists(function ($follows) use ($viewer) {
                                $follows->selectRaw('1')
                                    ->from('follows as story_author_follows_viewer')
                                    ->whereColumn('story_author_follows_viewer.follower_id', 'my_day_stories.user_id')
                                    ->where('story_author_follows_viewer.following_id', $viewer->id);
                            });
                    });
            });
    }
}
