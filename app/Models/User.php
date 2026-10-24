<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'profile_photo',
        'cover_photo',
        'bio',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_permanently_banned' => 'boolean',
            'banned_until' => 'datetime',
        ];
    }

    public function isAccountBanned(): bool
    {
        return $this->is_permanently_banned || ($this->banned_until?->isFuture() ?? false);
    }

    public function accountBanNotice(): string
    {
        $violation = str($this->ban_violation ?: 'community guidelines')->replace('_', ' ')->title();
        $reason = $this->ban_reason ?: 'No additional message was provided.';

        if ($this->is_permanently_banned) {
            return "Your NEX account has been permanently suspended for {$violation}. Moderator message: {$reason}";
        }

        $until = $this->banned_until?->timezone(config('app.timezone'));
        $remaining = $until?->diffForHumans() ?? 'an unknown duration';
        $date = $until?->format('M j, Y g:i A') ?? 'an unknown date';

        return "Your NEX account is temporarily suspended for {$violation}. Time remaining: {$remaining} (until {$date}). Moderator message: {$reason}";
    }

    public function moderationActions()
    {
        return $this->hasMany(ModerationAction::class)->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | Posts
    |--------------------------------------------------------------------------
    */

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function myDayStories()
    {
        return $this->hasMany(MyDayStory::class);
    }

    public function myDayHighlights()
    {
        return $this->hasMany(MyDayHighlight::class)->with('stories');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    public function postReactions() {
        return $this->hasMany(PostReaction::class);
    }

    public function commentReactions() {
        return $this->hasMany(CommentReaction::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Followers
    |--------------------------------------------------------------------------
    */

    public function followers()
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'following_id',
            'follower_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Following
    |--------------------------------------------------------------------------
    */

    public function following()
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'follower_id',
            'following_id'
        );
    }
}
