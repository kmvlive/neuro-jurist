@extends('layouts.app')

@section('title', 'Выручка — Админ-панель')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">📊 Отчёт по выручке</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-primary hover:underline">← В админку</a>
    </div>

    {{-- Верхние карточки --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Выручка за {{ $ruMonths[now()->month - 1] }}</div>
            <div class="text-xl sm:text-2xl font-bold text-green-600">{{ number_format($revenueMonth / 100, 0, ',', ' ') }} ₽</div>
            @if($growth !== null)
                <div class="text-xs mt-1 {{ $growth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $growth >= 0 ? '▲' : '▼' }} {{ abs($growth) }}% к прошлому месяцу
                </div>
            @endif
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Платежей в этом месяце</div>
            <div class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ $paymentsMonth }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">оплативших клиентов: {{ $payingMonth }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Средний чек (месяц)</div>
            <div class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($avgCheckMonth / 100, 0, ',', ' ') }} ₽</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">всего времени: {{ number_format($avgCheckTotal / 100, 0, ',', ' ') }} ₽</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Конверсия в оплату</div>
            <div class="text-xl sm:text-2xl font-bold {{ ($conversion ?? 0) >= 5 ? 'text-green-600' : 'text-orange-600' }}">{{ $conversion !== null ? $conversion . '%' : '—' }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">регистраций: {{ $usersMonth }} → оплат: {{ $payingMonth }}</div>
        </div>
    </div>

    {{-- График по дням --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Выручка по дням (30 дней)</h2>
        <div class="flex items-end gap-0.5 sm:gap-1 h-40">
            @foreach($days as $d)
                <div class="flex-1 h-full flex flex-col justify-end" title="{{ $d['label'] }}: {{ number_format($d['value'] / 100, 0, ',', ' ') }} ₽">
                    <div class="w-full bg-primary rounded-t {{ $d['value'] ? '' : 'opacity-10' }}"
                         style="height: {{ $d['value'] ? max(4, round($d['value'] / $maxDay * 100)) : 2 }}%"></div>
                </div>
            @endforeach
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-2">
            <span>{{ $days[0]['label'] }}</span>
            <span>{{ $days[14]['label'] }}</span>
            <span>{{ $days[29]['label'] }}</span>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-8">
        {{-- По тарифам --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Продажи по тарифам (всё время)</h2>
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Тариф</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Продаж</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Выручка</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Доля</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($plans as $p)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ ucfirst($p->plan) }}</td>
                            <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">{{ $p->cnt }}</td>
                            <td class="px-3 py-2 text-right font-medium text-gray-900 dark:text-white">{{ number_format($p->revenue / 100, 0, ',', ' ') }} ₽</td>
                            <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">{{ $revenueTotal ? round($p->revenue / $revenueTotal * 100) : 0 }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">Продаж пока нет</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{-- Промокоды --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Эффективность промокодов</h2>
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Код</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Исп.</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Выручка</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Скидки</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($promos as $p)
                        <tr>
                            <td class="px-3 py-2 font-mono font-medium text-gray-900 dark:text-white">{{ $p->promo_code }}</td>
                            <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">{{ $p->cnt }}</td>
                            <td class="px-3 py-2 text-right font-medium text-gray-900 dark:text-white">{{ number_format($p->revenue / 100, 0, ',', ' ') }} ₽</td>
                            <td class="px-3 py-2 text-right text-orange-600">−{{ number_format($p->discount / 100, 0, ',', ' ') }} ₽</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">Промокоды ещё не использовались</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

    {{-- По месяцам --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Выручка по месяцам (12 месяцев)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Месяц</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Платежей</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Выручка</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Средний чек</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach(array_reverse($months) as $m)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $m['label'] }}</td>
                            <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">{{ $m['count'] }}</td>
                            <td class="px-3 py-2 text-right font-medium {{ $m['revenue'] ? 'text-green-600' : 'text-gray-400' }}">{{ number_format($m['revenue'] / 100, 0, ',', ' ') }} ₽</td>
                            <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-400">{{ $m['avg'] ? number_format($m['avg'] / 100, 0, ',', ' ') . ' ₽' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex flex-wrap gap-6 text-sm">
        <div><span class="text-gray-500 dark:text-gray-400">Всего выручка:</span> <strong class="text-green-600">{{ number_format($revenueTotal / 100, 0, ',', ' ') }} ₽</strong></div>
        <div><span class="text-gray-500 dark:text-gray-400">Всего платежей:</span> <strong>{{ $paymentsTotal }}</strong></div>
    </div>
</div>
@endsection
