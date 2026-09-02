<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quick_prompts', function (Blueprint $table) {
            $table->text('example_answers')->nullable()->after('example_questions');
        });
    }

    public function down(): void
    {
        Schema::table('quick_prompts', function (Blueprint $table) {
            $table->dropColumn('example_answers');
        });
    }
};
