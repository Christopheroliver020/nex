<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // The original feature migration may already be recorded on databases
        // whose tables were later removed or restored from an incomplete dump.
        if (! Schema::hasTable('my_day_story_views')) {
            Schema::create('my_day_story_views', function (Blueprint $table) {
                $table->id();
                $table->foreignId('story_id')->constrained('my_day_stories')->cascadeOnDelete();
                $table->foreignId('viewer_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('viewed_at')->useCurrent();
                $table->unique(['story_id', 'viewer_id']);
            });
        }

        if (! Schema::hasTable('my_day_highlights')) {
            Schema::create('my_day_highlights', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('title', 50);
                $table->string('description', 160)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('my_day_highlight_stories')) {
            Schema::create('my_day_highlight_stories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('highlight_id')->constrained('my_day_highlights')->cascadeOnDelete();
                $table->foreignId('story_id')->constrained('my_day_stories')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['highlight_id', 'story_id']);
            });
        }
    }

    public function down(): void
    {
        // This repair may have skipped tables created by the original feature
        // migration, so dropping them here would make a rollback destructive.
    }
};
