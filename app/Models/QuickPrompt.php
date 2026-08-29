<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickPrompt extends Model
{
    protected $fillable = ['category_id', 'title', 'key', 'icon', 'text', 'active', 'sort_order'];

    public function category()
    {
        return $this->belongsTo(PromptCategory::class);
    }

    public function ad()
    {
        return $this->hasOne(Ad::class);
    }

    public static function getActive()
    {
        return self::where('active', true)->orderBy('sort_order')->get();
    }
}
