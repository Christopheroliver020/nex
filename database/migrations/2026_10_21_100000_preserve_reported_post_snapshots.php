<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_reports', function (Blueprint $table) {
            $table->foreignId('post_author_id')->nullable()->after('post_id')->constrained('users')->nullOnDelete();
            $table->text('reported_content')->nullable()->after('post_author_id');
            $table->string('reported_media_path')->nullable()->after('reported_content');
            $table->string('reported_media_type', 20)->nullable()->after('reported_media_path');
        });

        DB::table('post_reports')
            ->join('posts', 'post_reports.post_id', '=', 'posts.id')
            ->update([
                'post_reports.post_author_id' => DB::raw('posts.user_id'),
                'post_reports.reported_content' => DB::raw('posts.content'),
                'post_reports.reported_media_path' => DB::raw('posts.media_path'),
                'post_reports.reported_media_type' => DB::raw('posts.media_type'),
            ]);

        Schema::table('post_reports', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->unsignedBigInteger('post_id')->nullable()->change();
            $table->foreign('post_id')->references('id')->on('posts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('post_reports', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->dropConstrainedForeignId('post_author_id');
            $table->dropColumn(['reported_content', 'reported_media_path', 'reported_media_type']);
            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
        });
    }
};
