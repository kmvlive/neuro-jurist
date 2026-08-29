<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FooterLink extends Model
{
    protected $fillable = [
        'title',
        'url',
        'is_external',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_external' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function getActiveLinks()
    {
        return Cache::remember('footer_links', 3600, function () {
            return self::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }

    public static function clearCache()
    {
        Cache::forget('footer_links');
    }
}
