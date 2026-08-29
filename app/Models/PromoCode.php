<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code', 'discount_percent', 'active', 'max_uses', 'used_count', 'expires_at',
        'one_per_user', 'new_users_only', 'user_id'
    ];

    protected $casts = [
        'active' => 'boolean',
        'expires_at' => 'datetime',
        'one_per_user' => 'boolean',
        'new_users_only' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValid(?User $user = null): bool
    {
        if (!$this->active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return false;
        
        // Персональный код — только для указанного пользователя
        if ($this->user_id && $user && $this->user_id !== $user->id) return false;
        
        // Только для новых клиентов
        if ($this->new_users_only && $user) {
            $hasActiveSub = $user->subscription_ends_at && $user->subscription_ends_at->isFuture();
            $hasAnyPayment = Payment::where('user_id', $user->id)->where('status', 'CONFIRMED')->exists();
            if ($hasActiveSub || $hasAnyPayment) return false;
        }
        
        // Один раз на пользователя
        if ($this->one_per_user && $user) {
            $alreadyUsed = Payment::where('user_id', $user->id)
                ->where('promo_code', $this->code)
                ->where('status', 'CONFIRMED')
                ->exists();
            if ($alreadyUsed) return false;
        }
        
        return true;
    }

    public function getValidationMessage(?User $user = null): string
    {
        if (!$this->active) return 'Промокод больше не активен';
        if ($this->expires_at && $this->expires_at->isPast()) return 'Срок действия промокода истёк';
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return 'Лимит использований исчерпан';
        if ($this->user_id && $user && $this->user_id !== $user->id) return 'Этот промокод не для вас';
        if ($this->new_users_only && $user) {
            $hasActiveSub = $user->subscription_ends_at && $user->subscription_ends_at->isFuture();
            $hasAnyPayment = Payment::where('user_id', $user->id)->where('status', 'CONFIRMED')->exists();
            if ($hasActiveSub || $hasAnyPayment) return 'Промокод только для новых клиентов';
        }
        if ($this->one_per_user && $user) {
            $alreadyUsed = Payment::where('user_id', $user->id)
                ->where('promo_code', $this->code)
                ->where('status', 'CONFIRMED')
                ->exists();
            if ($alreadyUsed) return 'Вы уже использовали этот промокод';
        }
        return 'Промокод недействителен';
    }

    public function discountedPrice(int $price): int
    {
        return (int) round($price * (100 - $this->discount_percent) / 100);
    }
}
