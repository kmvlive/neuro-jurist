<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'free_messages_used',
        'subscription_plan',
        'subscription_ends_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_ends_at' => 'datetime',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is client
     */
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Check if user has active subscription
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->subscription_plan === null) {
            return false;
        }

        if ($this->subscription_ends_at === null) {
            return true;
        }

        return $this->subscription_ends_at->isFuture();
    }

    /**
     * Check if user can send messages (has free messages or active subscription)
     */
    public function canSendMessages(): bool
    {
        if ($this->hasActiveSubscription()) {
            return true;
        }

        return $this->free_messages_used < 20;
    }

    /**
     * Get remaining free messages
     */
    public function getRemainingFreeMessages(): int
    {
        if ($this->hasActiveSubscription()) {
            return PHP_INT_MAX;
        }

        return max(0, 20 - $this->free_messages_used);
    }

    /**
     * Increment free messages used counter
     */
    public function incrementFreeMessagesUsed(): void
    {
        if (!$this->hasActiveSubscription() && $this->free_messages_used < 20) {
            $this->increment('free_messages_used');
        }
    }

    /**
     * Chat relationship
     */
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
}
