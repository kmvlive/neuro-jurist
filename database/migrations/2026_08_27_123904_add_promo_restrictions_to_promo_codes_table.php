<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->boolean('one_per_user')->default(false)->after('max_uses');
            $table->boolean('new_users_only')->default(false)->after('one_per_user');
            $table->foreignId('user_id')->nullable()->after('new_users_only')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['one_per_user', 'new_users_only', 'user_id']);
        });
    }
};
