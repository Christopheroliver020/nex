<?php

namespace App\Console\Commands;

use App\Models\MyDayStory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneExpiredMyDayStories extends Command
{
    protected $signature = 'myday:prune-expired';

    protected $description = 'Delete expired My Day stories and their uploaded media';

    public function handle(): int
    {
        MyDayStory::query()
            ->where('expires_at', '<=', now())
            ->whereDoesntHave('highlights')
            ->chunkById(100, function ($stories): void {
                foreach ($stories as $story) {
                    Storage::disk('local')->delete(array_filter([$story->media_path, $story->music_path]));
                    $story->delete();
                }
            });

        $this->info('Expired My Day stories were removed.');

        return self::SUCCESS;
    }
}
