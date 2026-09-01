@extends('layouts.app')

@section('title', 'AI-статистика — Админ-панель')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-primary hover:underline">← Админ-панель</a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mt-4">📊 AI-статистика</h1>
    </div>

    {{-- Фильтр периода --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6 flex items-center gap-2 flex-wrap">
        <span class="text-gray-700 dark:text-gray-300">Период:</span>
        @foreach([1, 3, 7, 14, 30] as $d)
            <a href="{{ route('admin.ai-usage.index', ['days' => $d]) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition
                      {{ $days == $d ? 'bg-primary text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                @if($d == 1) Сегодня @elseif($d == 3) 3 дня @elseif($d == 7) 7 дней @elseif($d == 14) 14 дней @else 30 дней @endif
            </a>
        @endforeach
    </div>

    {{-- Ключевые метрики --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Запросов</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                {{ number_format($stats->total_requests ?? 0, 0, ',', ' ') }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Токенов</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                {{ number_format($stats->total_tokens ?? 0, 0, ',', ' ') }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Ср. время ответа</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                {{ round(($stats->avg_total_ms ?? 0) / 1000, 1) }}с
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Ср. до первого чанка</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">
                {{ round(($stats->avg_first_chunk_ms ?? 0) / 1000, 1) }}с
            </p>
        </div>
    </div>

    {{-- График по дням --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📈 Активность по дням</h2>
        <canvas id="dailyChart" height="80"></canvas>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Топ моделей --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🏆 Топ моделей</h2>
            @if($topModels->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">Нет данных за выбранный период</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="pb-2">Модель</th>
                                <th class="pb-2 text-right">Запросов</th>
                                <th class="pb-2 text-right">Ср. время</th>
                                <th class="pb-2 text-right">Токенов</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 dark:text-gray-300">
                            @foreach($topModels as $m)
                                <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                    <td class="py-2 font-mono text-xs">{{ $m->model }}</td>
                                    <td class="py-2 text-right font-semibold">{{ number_format($m->requests) }}</td>
                                    <td class="py-2 text-right">
                                        {{ round(($m->avg_first_chunk_ms ?? 0) / 1000, 1) }}с
                                    </td>
                                    <td class="py-2 text-right text-gray-500 dark:text-gray-400">
                                        {{ number_format($m->tokens) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Последние запросы --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">⏱️ Последние запросы</h2>
            @if($recentLogs->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">Нет данных</p>
            @else
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($recentLogs->take(10) as $log)
                        <div class="flex items-center gap-3 text-xs p-2 rounded bg-gray-50 dark:bg-gray-700/30">
                            <div class="flex-1 min-w-0">
                                <div class="font-mono text-gray-700 dark:text-gray-300 truncate">{{ $log->model }}</div>
                                <div class="text-gray-500 dark:text-gray-400">
                                    {{ $log->created_at->format('d.m H:i') }} ·
                                    {{ $log->type }} ·
                                    @if($log->first_chunk_ms)
                                        {{ round($log->first_chunk_ms / 1000, 1) }}с
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ number_format(($log->prompt_tokens ?? 0) + ($log->completion_tokens ?? 0)) }}
                                </div>
                                <div class="text-gray-500 dark:text-gray-400">токенов</div>
                            </div>
                            @if($log->success)
                                <span class="text-green-500">✓</span>
                            @else
                                <span class="text-red-500" title="{{ $log->error }}">✗</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const dailyData = @json($dailyUsage);
    const ctx = document.getElementById('dailyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dailyData.map(d => d.date),
            datasets: [
                {
                    label: 'Запросов',
                    data: dailyData.map(d => d.requests),
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    yAxisID: 'y',
                },
                {
                    type: 'line',
                    label: 'Токенов (тыс.)',
                    data: dailyData.map(d => Math.round(d.tokens / 1000)),
                    borderColor: 'rgba(249, 115, 22, 1)',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    tension: 0.3,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { position: 'left', title: { display: true, text: 'Запросов' } },
                y1: { position: 'right', title: { display: true, text: 'Токенов (тыс.)' }, grid: { drawOnChartArea: false } }
            }
        }
    });
</script>
@endsection
