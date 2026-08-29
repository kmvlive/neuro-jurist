<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;

class AdminRevenueController extends Controller
{
    public function index()
    {
        $ruMonths = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
        // === Текущий и прошлый месяц ===
        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();

        $revenueMonth = (int) Payment::where('status', 'CONFIRMED')->where('created_at', '>=', $monthStart)->sum('amount');
        $revenueLastMonth = (int) Payment::where('status', 'CONFIRMED')->whereBetween('created_at', [$lastMonthStart, $monthStart])->sum('amount');
        $paymentsMonth = Payment::where('status', 'CONFIRMED')->where('created_at', '>=', $monthStart)->count();
        $avgCheckMonth = $paymentsMonth ? intdiv($revenueMonth, $paymentsMonth) : 0;
        $growth = $revenueLastMonth > 0 ? round(($revenueMonth - $revenueLastMonth) / $revenueLastMonth * 100) : null;

        // === Конверсия ===
        $usersMonth = User::where('created_at', '>=', $monthStart)->count();
        $payingMonth = Payment::where('status', 'CONFIRMED')->where('created_at', '>=', $monthStart)->distinct('user_id')->count('user_id');
        $conversion = $usersMonth > 0 ? round($payingMonth / $usersMonth * 100, 1) : null;

        // === По дням (30 дней) ===
        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $sum = (int) Payment::where('status', 'CONFIRMED')
                ->whereBetween('created_at', [$d->copy()->startOfDay(), $d->copy()->endOfDay()])
                ->sum('amount');
            $days[] = ['label' => $d->format('d.m'), 'value' => $sum];
        }
        $maxDay = max(max(array_column($days, 'value')), 1);

        // === По месяцам (12 месяцев) ===
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $sum = (int) Payment::where('status', 'CONFIRMED')->whereBetween('created_at', [$start, $end])->sum('amount');
            $cnt = Payment::where('status', 'CONFIRMED')->whereBetween('created_at', [$start, $end])->count();
            $months[] = [
                'label' => $ruMonths[$start->month - 1] . ' ' . $start->format('Y'),
                'revenue' => $sum,
                'count' => $cnt,
                'avg' => $cnt ? intdiv($sum, $cnt) : 0,
            ];
        }

        // === По тарифам ===
        $plans = Payment::where('status', 'CONFIRMED')
            ->selectRaw('plan, count(*) as cnt, sum(amount) as revenue')
            ->groupBy('plan')->orderByDesc('revenue')->get();

        // === Промокоды ===
        $promos = Payment::where('status', 'CONFIRMED')->whereNotNull('promo_code')
            ->selectRaw('promo_code, count(*) as cnt, sum(amount) as revenue, sum(COALESCE(original_amount, amount) - amount) as discount')
            ->groupBy('promo_code')->orderByDesc('revenue')->limit(10)->get();

        $revenueTotal = (int) Payment::where('status', 'CONFIRMED')->sum('amount');
        $paymentsTotal = Payment::where('status', 'CONFIRMED')->count();
        $avgCheckTotal = $paymentsTotal ? intdiv($revenueTotal, $paymentsTotal) : 0;

        return view('admin.revenue', compact(
            'revenueMonth', 'revenueLastMonth', 'paymentsMonth', 'avgCheckMonth', 'growth',
            'usersMonth', 'payingMonth', 'conversion',
            'days', 'maxDay', 'months', 'plans', 'promos',
            'revenueTotal', 'paymentsTotal', 'avgCheckTotal', 'ruMonths'
        ));
    }
}
