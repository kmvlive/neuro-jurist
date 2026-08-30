<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prompt_category_quick_prompt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prompt_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('quick_prompt_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['prompt_category_id', 'quick_prompt_id'], 'pc_qp_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prompt_category_quick_prompt');
    }
};
