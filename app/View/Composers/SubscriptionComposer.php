<?php

namespace App\View\Composers;

use Illuminate\View\View;

class SubscriptionComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();
        
        if (!$user || !$user->subscription_ends_at) {
            $view->with('subscriptionDaysLeft', null);
            return;
        }

        $endsAt = \Carbon\Carbon::parse($user->subscription_ends_at);
        $now = now();
        
        if ($endsAt->isPast()) {
            $view->with('subscriptionDaysLeft', 0);
        } else {
            $days = $now->diffInDays($endsAt, false);
            $view->with('subscriptionDaysLeft', (int) ceil($days));
        }
    }
}
