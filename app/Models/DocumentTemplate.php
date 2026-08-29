<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'key', 'title', 'description', 'icon', 'category',
        'questions', 'prompt_template', 'active', 'sort_order',
    ];

    protected $casts = [
        'questions' => 'array',
        'active' => 'boolean',
    ];

    public static function getActive()
    {
        return self::where('active', true)->orderBy('sort_order')->get();
    }

    public static function findByKey(string $key)
    {
        return self::where('key', $key)->first();
    }
}
