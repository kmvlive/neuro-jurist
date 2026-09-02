<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quick_prompts', function (Blueprint $table) {
            $table->string('seo_title', 255)->nullable()->after('sort_order');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->text('seo_text')->nullable()->after('seo_description');
            $table->text('example_questions')->nullable()->after('seo_text');
        });
    }

    public function down(): void
    {
        Schema::table('quick_prompts', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'seo_text', 'example_questions']);
        });
    }
};
