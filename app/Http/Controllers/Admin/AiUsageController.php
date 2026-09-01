<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AiUsageController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->get('days', 7);
        $since = Carbon::now()->subDays($days);

        // === Ключевые метрики за период ===
        $stats = AiUsageLog::where('created_at', '>=', $since)
            ->where('success', true)
            ->selectRaw('
                COUNT(*) as total_requests,
                SUM(prompt_tokens + completion_tokens) as total_tokens,
                SUM(total_ms) as total_ms,
                AVG(first_chunk_ms) as avg_first_chunk_ms,
                AVG(total_ms) as avg_total_ms
            ')
            ->first();

        // === Топ моделей по количеству запросов ===
        $topModels = AiUsageLog::where('created_at', '>=', $since)
            ->where('success', true)
            ->select('model', 
                DB::raw('COUNT(*) as requests'),
                DB::raw('SUM(prompt_tokens + completion_tokens) as tokens'),
                DB::raw('AVG(first_chunk_ms) as avg_first_chunk_ms')
            )
            ->groupBy('model')
            ->orderByDesc('requests')
            ->limit(10)
            ->get();

        // === Графики по дням (количество запросов и токенов) ===
        $dailyUsage = AiUsageLog::where('created_at', '>=', $since)
            ->where('success', true)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as requests, SUM(prompt_tokens + completion_tokens) as tokens')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // === Последние 20 запросов ===
        $recentLogs = AiUsageLog::with(['chat', 'user'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // === Расчёт стоимости (с учётом config/ai.php) ===
        $totalCost = $recentLogs->where('created_at', '>=', $since)->sum(fn($log) => $log->costRub());
        $topModels = $topModels->map(function ($m) {
            $log = new AiUsageLog();
            $log->model = $m->model;
            $log->prompt_tokens = AiUsageLog::where('model', $m->model)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->sum('prompt_tokens');
            $log->completion_tokens = AiUsageLog::where('model', $m->model)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->sum('completion_tokens');
            $m->cost_rub = $log->costRub();
            return $m;
        });

        return view('admin.ai-usage.index', compact(
            'stats', 'topModels', 'dailyUsage', 'recentLogs', 'days', 'totalCost'
        ));
    }
}
