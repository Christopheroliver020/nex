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
            $table->dropForeign(['post_id']);
            $table->dropUnique('post_reports_post_id_user_id_unique');
        });

        Schema::table('post_reports', function (Blueprint $table) {
            $table->index('post_id');
            $table->foreign('post_id')->references('id')->on('posts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $hasProfileReports = DB::table('post_reports')->whereNull('post_id')->exists();
        $hasRepeatReports = DB::table('post_reports')
            ->select('post_id', 'user_id')
            ->groupBy('post_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasProfileReports || $hasRepeatReports) {
            throw new RuntimeException('Cannot restore the one-report-per-post constraint while profile or repeat reports exist.');
        }

        Schema::table('post_reports', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->dropIndex(['post_id']);
            $table->unique(['post_id', 'user_id']);
            $table->foreign('post_id')->references('id')->on('posts')->nullOnDelete();
        });
    }
};
