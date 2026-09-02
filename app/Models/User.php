<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'free_messages_used',
        'subscription_plan',
        'subscription_ends_at',
        'unlimited_messages',
        'last_login_at',
        'automation_sent',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_ends_at' => 'datetime',
        'automation_sent' => 'array',
            'unlimited_messages' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Безлимитные сообщения (включено админом вручную)
     */
    public function hasUnlimitedMessages(): bool
    {
        return (bool) $this->unlimited_messages;
    }

    public function hasActiveSubscription(): bool
    {
        if ($this->subscription_plan === null) {
            return false;
        }

        if ($this->subscription_plan === 'start') {
            return false;
        }

        if ($this->subscription_ends_at === null) {
            return true;
        }

        return $this->subscription_ends_at->isFuture();
    }

    public function canSendMessages(): bool
    {
        if ($this->hasActiveSubscription() || $this->hasUnlimitedMessages()) {
            return true;
        }

        return $this->free_messages_used < 20;
    }

    public function getRemainingFreeMessages(): int
    {
        if ($this->hasActiveSubscription() || $this->hasUnlimitedMessages()) {
            return PHP_INT_MAX;
        }

        return max(0, 20 - $this->free_messages_used);
    }

    public function incrementFreeMessagesUsed(): void
    {
        if (!$this->hasActiveSubscription() && !$this->hasUnlimitedMessages() && $this->free_messages_used < 20) {
            $this->increment('free_messages_used');
        }
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
}
