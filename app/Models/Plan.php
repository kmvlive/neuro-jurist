<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'key',
        'name',
        'price',
        'old_price',
        'duration_days',
        'period',
        'currency',
        'features',
        'limitations',
        'highlighted',
        'button_text',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'limitations' => 'array',
        'highlighted' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'integer',
        'old_price' => 'integer',
        'duration_days' => 'integer',
        'sort_order' => 'integer',
    ];

    public function isFree(): bool
    {
        return $this->price == 0;
    }

    public function hasDiscount(): bool
    {
        return $this->old_price && $this->old_price > $this->price;
    }

    public function getDiscountPercent(): int
    {
        if (!$this->hasDiscount()) return 0;
        return round((1 - $this->price / $this->old_price) * 100);
    }

    public function getPeriodLabel(): string
    {
        if ($this->price == 0) return 'бесплатно';
        if ($this->duration_days == 1) return 'день';
        if ($this->duration_days == 7) return 'неделя';
        if ($this->duration_days == 30 || $this->duration_days == 31) return 'мес';
        if ($this->duration_days == 90) return '3 мес';
        if ($this->duration_days == 180) return '6 мес';
        if ($this->duration_days >= 365) return 'год';
        return $this->duration_days . ' дн.';
    }

    public static function getActivePlans()
    {
        return cache()->remember('active_plans', 3600, function () {
            return self::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($plan) {
                    return [
                        'key' => $plan->key,
                        'name' => $plan->name,
                        'price' => $plan->price,
                        'old_price' => $plan->old_price,
                        'currency' => $plan->currency,
                        'period' => $plan->getPeriodLabel(),
                        'features' => $plan->features ?? [],
                        'limitations' => $plan->limitations ?? [],
                        'highlighted' => $plan->highlighted,
                        'buttonText' => $plan->button_text,
                        'duration_days' => $plan->duration_days,
                    ];
                })
                ->values()
                ->toArray();
        });
    }

    public static function clearCache(): void
    {
        cache()->forget('active_plans');
    }
}
