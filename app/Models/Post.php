<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'content',
        'media_path',
        'media_type',
        'audience',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->whereNull('parent_id')
            ->latest();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PostReport::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(PostShare::class);
    }

    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        return $query->whereHas('user', fn (Builder $author) => $author
            ->where('is_permanently_banned', false)
            ->where(fn (Builder $active) => $active->whereNull('banned_until')->orWhere('banned_until', '<=', now())))
            ->where(function (Builder $query) use ($viewer) {
            $query->where('user_id', $viewer->id)
                ->orWhere('audience', 'public')
                ->orWhere(function (Builder $query) use ($viewer) {
                    $query->where('audience', 'friends')
                        ->whereExists(function ($follows) use ($viewer) {
                            $follows->selectRaw('1')
                                ->from('follows as viewer_follows_author')
                                ->whereColumn('viewer_follows_author.following_id', 'posts.user_id')
                                ->where('viewer_follows_author.follower_id', $viewer->id);
                        })
                        ->whereExists(function ($follows) use ($viewer) {
                            $follows->selectRaw('1')
                                ->from('follows as author_follows_viewer')
                                ->whereColumn('author_follows_viewer.follower_id', 'posts.user_id')
                                ->where('author_follows_viewer.following_id', $viewer->id);
                        });
                });
        });
    }
}
