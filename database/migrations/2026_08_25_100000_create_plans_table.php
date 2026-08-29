<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->integer('price')->default(0);
            $table->string('period')->default('мес');
            $table->string('currency')->default('₽');
            $table->json('features')->nullable();
            $table->json('limitations')->nullable();
            $table->boolean('highlighted')->default(false);
            $table->string('button_text')->default('Выбрать');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
