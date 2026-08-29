<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'free_messages_used')) {
                $table->integer('free_messages_used')->default(0);
            }
            if (!Schema::hasColumn('users', 'subscription_plan')) {
                $table->string('subscription_plan')->nullable();
            }
            if (!Schema::hasColumn('users', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['free_messages_used', 'subscription_plan', 'subscription_ends_at']);
        });
    }
};
