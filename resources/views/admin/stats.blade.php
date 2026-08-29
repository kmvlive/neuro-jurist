@extends('layouts.app')

@section('title', 'Статистика — Админ-панель')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-primary hover:underline">← Админ-панель</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-4">📊 Статистика</h1>
    </div>

    <h2 class="text-xl font-semibold mb-4">Посещаемость</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-primary">{{ $visitsToday }}</div>
            <div class="text-sm text-gray-600">Сегодня</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-primary">{{ $visitsWeek }}</div>
            <div class="text-sm text-gray-600">За 7 дней</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-primary">{{ $visitsMonth }}</div>
            <div class="text-sm text-gray-600">За 30 дней</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $uniqueMonth }}</div>
            <div class="text-sm text-gray-600">Уникальных (30 дн)</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="font-semibold mb-4">Визиты за 14 дней</h3>
        <canvas id="trafficChart" height="90"></canvas>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold mb-4">Популярные страницы (30 дн)</h3>
            <div class="space-y-2">
                @forelse($topPages as $page)
                    <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
                        <span class="text-gray-800">{{ $page->path }}</span>
                        <span class="font-semibold text-primary">{{ $page->views }}</span>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">Данных пока нет</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold mb-4">Бизнес-метрики</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-600">Пользователи (новых за 30 дн)</span>
                    <span class="font-semibold">{{ $usersTotal }} ({{ $usersNewMonth }})</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-600">Чатов / сообщений (сегодня)</span>
                    <span class="font-semibold">{{ $chatsTotal }} / {{ $messagesTotal }} ({{ $messagesToday }})</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-600">Загружено документов</span>
                    <span class="font-semibold">{{ $filesTotal }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <span class="text-gray-600">Оплат (выручка за 30 дн)</span>
                    <span class="font-semibold text-green-600">{{ $paymentsCount }} ({{ number_format($revenueMonth / 100, 0, ',', ' ') }} ₽)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Выручка всего</span>
                    <span class="font-semibold text-green-600">{{ number_format($revenueTotal / 100, 0, ',', ' ') }} ₽</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('trafficChart'), {
        type: 'line',
        data: {
            labels: @json($days),
            datasets: [{
                label: 'Визиты',
                data: @json($counts),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            plugins: { legend: { display: false } }
        }
    });
</script>
@endpush
