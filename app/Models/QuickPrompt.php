<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickPrompt extends Model
{
    protected $fillable = ['title', 'key', 'icon', 'text', 'active', 'show_in_chat', 'sort_order', 'seo_title', 'seo_description', 'seo_text', 'example_questions'];

    protected $casts = [
        'active' => 'boolean',
        'show_in_chat' => 'boolean',
        'example_questions' => 'array',
    ];

    public function categories()
    {
        return $this->belongsToMany(PromptCategory::class, 'prompt_category_quick_prompt');
    }

    public function ad()
    {
        return $this->hasOne(Ad::class);
    }

    public static function getActive()
    {
        return self::where('active', true)->orderBy('sort_order')->get();
    }

    public static function getForChat()
    {
        return self::where('active', true)
            ->where('show_in_chat', true)
            ->orderBy('sort_order')
            ->get();
    }
}