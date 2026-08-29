<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromptCategory extends Model
{
    protected $fillable = ['parent_id', 'name', 'slug', 'icon', 'sort_order', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function parent()
    {
        return $this->belongsTo(PromptCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PromptCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function quickPrompts()
    {
        return $this->hasMany(QuickPrompt::class, 'category_id');
    }

    public static function sections()
    {
        return self::whereNull('parent_id')->where('active', true)->orderBy('sort_order')->get();
    }
}
