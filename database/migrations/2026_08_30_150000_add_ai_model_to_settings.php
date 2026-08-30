<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        Setting::updateOrCreate(
            ['key' => 'ai_model'],
            ['value' => 'deepseek/deepseek-v4-flash']
        );
    }

    public function down(): void
    {
        Setting::where('key', 'ai_model')->delete();
    }
};
