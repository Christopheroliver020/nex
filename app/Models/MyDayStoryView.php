<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyDayStoryView extends Model
{
    public $timestamps = false;
    protected $fillable = ['story_id', 'viewer_id', 'viewed_at'];
    protected function casts(): array { return ['viewed_at' => 'datetime']; }
}
