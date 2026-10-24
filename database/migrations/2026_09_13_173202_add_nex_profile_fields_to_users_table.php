<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('username')->unique()->nullable()->after('name');

            $table->string('profile_photo')->nullable()->after('username');

            $table->text('bio')->nullable()->after('profile_photo');

            $table->string('status', 100)->nullable()->after('bio');

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'username',
                'profile_photo',
                'bio',
                'status',
            ]);

        });
    }
};
