<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostReport extends Model
{
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    protected $fillable = [
        'post_id', 'user_id', 'reason', 'details', 'status', 'violation', 'decision',
        'resolution_note', 'reviewed_by', 'resolved_at', 'post_author_id',
        'reported_content', 'reported_media_path', 'reported_media_type',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'post_author_id');
    }
}
