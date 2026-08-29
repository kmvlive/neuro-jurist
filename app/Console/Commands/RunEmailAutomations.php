<?php

namespace App\Console\Commands;

use App\Mail\WelcomeEmail;
use App\Mail\Reminder3DaysEmail;
use App\Mail\LastChance7DaysEmail;
use App\Mail\ReactivationEmail;
use App\Mail\Missing60DaysEmail;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RunEmailAutomations extends Command
{
    protected $signature = 'email:automations';
    protected $description = 'Запуск автоматических email-рассылок по триггерам';

    public function handle()
    {
        $now = now();
        $stats = ['welcome' => 0, 'reminder' => 0, 'last_chance' => 0, 'reactivation' => 0, 'missing' => 0];

        // === 1. Welcome (только что зарегистрировался, не платил) ===
        $newUsers = User::where('created_at', '>=', $now->copy()->subHour())
            ->where('created_at', '<=', $now)
            ->get();
        
        foreach ($newUsers as $user) {
            $sent = $user->automation_sent ?? [];
            if (isset($sent['welcome'])) continue;
            
            $hasPayment = Payment::where('user_id', $user->id)->where('status', 'CONFIRMED')->exists();
            if ($hasPayment) continue;
            
            try {
                Mail::to($user->email)->send(new WelcomeEmail($user, 'WELCOME15'));
                $sent['welcome'] = $now->toIso8601String();
                $user->update(['automation_sent' => $sent]);
                $stats['welcome']++;
                Log::info("Welcome email sent", ['user' => $user->email]);
            } catch (\Throwable $e) {
                Log::error("Welcome email failed", ['user' => $user->email, 'error' => $e->getMessage()]);
            }
        }

        // === 2. Reminder 3 days (3 дня назад регистрация, не платил) ===
        $reminderUsers = User::whereBetween('created_at', [$now->copy()->subDays(3)->subHour(), $now->copy()->subDays(3)])
            ->get();
        
        foreach ($reminderUsers as $user) {
            $sent = $user->automation_sent ?? [];
            if (isset($sent['reminder'])) continue;
            
            $hasPayment = Payment::where('user_id', $user->id)->where('status', 'CONFIRMED')->exists();
            if ($hasPayment) continue;
            
            try {
                Mail::to($user->email)->send(new Reminder3DaysEmail($user, 'REMIND20'));
                $sent['reminder'] = $now->toIso8601String();
                $user->update(['automation_sent' => $sent]);
                $stats['reminder']++;
                Log::info("Reminder 3d sent", ['user' => $user->email]);
            } catch (\Throwable $e) {
                Log::error("Reminder 3d failed", ['user' => $user->email, 'error' => $e->getMessage()]);
            }
        }

        // === 3. Last chance 7 days ===
        $lastChanceUsers = User::whereBetween('created_at', [$now->copy()->subDays(7)->subHour(), $now->copy()->subDays(7)])
            ->get();
        
        foreach ($lastChanceUsers as $user) {
            $sent = $user->automation_sent ?? [];
            if (isset($sent['last_chance'])) continue;
            
            $hasPayment = Payment::where('user_id', $user->id)->where('status', 'CONFIRMED')->exists();
            if ($hasPayment) continue;
            
            try {
                Mail::to($user->email)->send(new LastChance7DaysEmail($user, 'LAST30'));
                $sent['last_chance'] = $now->toIso8601String();
                $user->update(['automation_sent' => $sent]);
                $stats['last_chance']++;
                Log::info("Last chance 7d sent", ['user' => $user->email]);
            } catch (\Throwable $e) {
                Log::error("Last chance 7d failed", ['user' => $user->email, 'error' => $e->getMessage()]);
            }
        }

        // === 4. Reactivation (подписка истекла 30 дней назад) ===
        $reactivationUsers = User::whereNotNull('subscription_ends_at')
            ->whereBetween('subscription_ends_at', [$now->copy()->subDays(30)->subHour(), $now->copy()->subDays(30)])
            ->get();
        
        foreach ($reactivationUsers as $user) {
            $sent = $user->automation_sent ?? [];
            if (isset($sent['reactivation'])) continue;
            
            if ($user->hasActiveSubscription()) continue;
            
            try {
                Mail::to($user->email)->send(new ReactivationEmail($user, 'BACK25'));
                $sent['reactivation'] = $now->toIso8601String();
                $user->update(['automation_sent' => $sent]);
                $stats['reactivation']++;
                Log::info("Reactivation sent", ['user' => $user->email]);
            } catch (\Throwable $e) {
                Log::error("Reactivation failed", ['user' => $user->email, 'error' => $e->getMessage()]);
            }
        }

        // === 5. Missing 60 days (нет входа 60 дней) ===
        $missingUsers = User::whereNotNull('last_login_at')
            ->where('last_login_at', '<', $now->copy()->subDays(60))
            ->get();
        
        foreach ($missingUsers as $user) {
            $sent = $user->automation_sent ?? [];
            if (isset($sent['missing'])) continue;
            
            try {
                Mail::to($user->email)->send(new Missing60DaysEmail($user));
                $sent['missing'] = $now->toIso8601String();
                $user->update(['automation_sent' => $sent]);
                $stats['missing']++;
                Log::info("Missing 60d sent", ['user' => $user->email]);
            } catch (\Throwable $e) {
                Log::error("Missing 60d failed", ['user' => $user->email, 'error' => $e->getMessage()]);
            }
        }

        $this->info(json_encode($stats));
        return Command::SUCCESS;
    }
}
