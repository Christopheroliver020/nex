<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MyDayHighlight extends Model
{
    protected $fillable = ['user_id', 'title', 'description'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function stories(): BelongsToMany
    {
        return $this->belongsToMany(MyDayStory::class, 'my_day_highlight_stories', 'highlight_id', 'story_id')->withTimestamps();
    }
}
