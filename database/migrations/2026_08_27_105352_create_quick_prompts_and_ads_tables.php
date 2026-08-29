<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('key', 50)->unique();
            $table->string('icon', 10)->default('📄');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quick_prompt_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->string('cta_text', 100)->nullable();
            $table->string('cta_url')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->string('prompt_key', 50)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn('prompt_key');
        });
        Schema::dropIfExists('ads');
        Schema::dropIfExists('quick_prompts');
    }
};
