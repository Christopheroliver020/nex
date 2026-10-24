<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('status');
            $table->boolean('is_permanently_banned')->default(false)->after('is_admin');
            $table->timestamp('banned_until')->nullable()->after('is_permanently_banned');
            $table->string('ban_violation', 60)->nullable()->after('banned_until');
            $table->text('ban_reason')->nullable()->after('ban_violation');
        });

        Schema::table('post_reports', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('details');
            $table->string('violation', 60)->nullable()->after('status');
            $table->string('decision', 30)->nullable()->after('violation');
            $table->text('resolution_note')->nullable()->after('decision');
            $table->foreignId('reviewed_by')->nullable()->after('resolution_note')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('reviewed_by');
        });

        Schema::create('moderation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('post_report_id')->nullable()->constrained('post_reports')->nullOnDelete();
            $table->string('action', 30);
            $table->string('violation', 60)->nullable();
            $table->text('reason')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_actions');

        Schema::table('post_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['status', 'violation', 'decision', 'resolution_note', 'resolved_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'is_permanently_banned', 'banned_until', 'ban_violation', 'ban_reason']);
        });
    }
};
