<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\PageView;
use App\Models\Payment;
use App\Models\User;

class AdminStatsController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $month = now()->subDays(29)->startOfDay();

        // === Трафик ===
        $visitsToday = PageView::where('created_at', '>=', $today)->count();
        $visitsWeek = PageView::where('created_at', '>=', now()->subDays(6)->startOfDay())->count();
        $visitsMonth = PageView::where('created_at', '>=', $month)->count();
        $uniqueMonth = PageView::where('created_at', '>=', $month)->distinct('ip')->count('ip');

        // График за 14 дней
        $days = [];
        $counts = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $days[] = $d->format('d.m');
            $counts[] = PageView::whereBetween('created_at', [$d->copy()->startOfDay(), $d->copy()->endOfDay()])->count();
        }

        // Популярные страницы
        $topPages = PageView::where('created_at', '>=', $month)
            ->selectRaw('path, count(*) as views')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        // === Бизнес-метрики ===
        $usersTotal = User::count();
        $usersNewMonth = User::where('created_at', '>=', $month)->count();
        $chatsTotal = Chat::count();
        $messagesTotal = Message::count();
        $messagesToday = Message::where('created_at', '>=', $today)->count();
        $filesTotal = Message::whereNotNull('file_name')->count();

        $revenueTotal = (int) Payment::where('status', 'CONFIRMED')->sum('amount');
        $revenueMonth = (int) Payment::where('status', 'CONFIRMED')->where('created_at', '>=', $month)->sum('amount');
        $paymentsCount = Payment::where('status', 'CONFIRMED')->count();

        return view('admin.stats', compact(
            'visitsToday', 'visitsWeek', 'visitsMonth', 'uniqueMonth',
            'days', 'counts', 'topPages',
            'usersTotal', 'usersNewMonth', 'chatsTotal', 'messagesTotal',
            'messagesToday', 'filesTotal', 'revenueTotal', 'revenueMonth', 'paymentsCount'
        ));
    }
}
