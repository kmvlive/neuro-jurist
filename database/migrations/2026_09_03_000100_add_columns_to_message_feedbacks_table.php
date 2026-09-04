<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_feedbacks', function (Blueprint $table) {
            $table->foreignId('message_id')->after('id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('vote')->after('message_id'); // 1 = 👍, -1 = 👎
            $table->text('comment')->nullable()->after('vote');
            $table->string('guest_id', 64)->nullable()->after('comment');
            $table->foreignId('user_id')->nullable()->after('guest_id')->constrained()->cascadeOnDelete();

            $table->index(['message_id', 'guest_id']);
            $table->index(['message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('message_feedbacks', function (Blueprint $table) {
            $table->dropIndex(['message_id', 'guest_id']);
            $table->dropIndex(['message_id', 'user_id']);
            $table->dropConstrainedForeignId('message_id');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['vote', 'comment', 'guest_id']);
        });
    }
};
