<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'duration_days')) {
                $table->integer('duration_days')->default(30)->after('price');
            }
        });

        // Обновим существующие тарифы
        DB::table('plans')->where('key', 'start')->update(['duration_days' => 30]);
        DB::table('plans')->where('key', 'profi')->update(['duration_days' => 30]);
        DB::table('plans')->where('key', 'business')->update(['duration_days' => 30]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('duration_days');
        });
    }
};
