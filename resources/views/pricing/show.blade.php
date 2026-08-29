@extends('layouts.app')

@section('title', 'Тарифы')

@section('content')
@php
    $user = auth()->user();
    $currentPlan = null;
    $currentPlanOrder = 0;
    $hasActiveSub = false;
    $planOrder = ['start' => 0, 'profi' => 1, 'business' => 2];

    if ($user) {
        $currentPlan = $user->subscription_plan;
        $currentPlanOrder = $planOrder[$currentPlan] ?? 0;
        $hasActiveSub = $user->hasActiveSubscription();
    }
@endphp

<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Выберите подходящий тариф</h1>
            <p class="text-xl text-gray-600">Начните с бесплатного тарифа или выберите профессиональный план</p>
        </div>

        @auth
            @if($hasActiveSub && $currentPlan)
            <div class="max-w-3xl mx-auto mb-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white bg-opacity-20 p-3 rounded-full">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm opacity-90">Ваша активная подписка</p>
                            <p class="text-xl font-bold">Тариф @if($currentPlan === 'profi') «Профи» @elseif($currentPlan === 'business') «Бизнес» @else «Старт» @endif</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm opacity-90">Действует до</p>
                        <p class="text-lg font-semibold">{{ $user->subscription_ends_at->format('d.m.Y') }}</p>
                    </div>
                </div>
            </div>
            @elseif($user && $user->hasUnlimitedMessages())
            <div class="max-w-3xl mx-auto mb-8 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center space-x-4">
                    <div class="bg-white bg-opacity-20 p-3 rounded-full">
                        <span class="text-2xl">&#9854;</span>
                    </div>
                    <div>
                        <p class="text-sm opacity-90">У вас безлимитный доступ</p>
                        <p class="text-xl font-bold">Сообщения не ограничены</p>
                    </div>
                </div>
            </div>
            @endif
        @endauth

        @if(session('success'))
        <div class="max-w-3xl mx-auto mb-6 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="max-w-3xl mx-auto mb-6 bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($plans as $index => $plan)
            @php
                $planKey = $plan["key"];
                $isCurrentPlan = $hasActiveSub && $currentPlan === $planKey;
                $isUpgrade = $hasActiveSub && ($planOrder[$planKey] ?? 0) > $currentPlanOrder;
                $isDowngrade = $hasActiveSub && ($planOrder[$planKey] ?? 0) < $currentPlanOrder;
            @endphp
            <div class="relative bg-white rounded-2xl shadow-xl overflow-hidden transform transition-all hover:scale-105 {{ $isCurrentPlan ? 'ring-4 ring-green-500 scale-105' : ($plan['highlighted'] ? 'ring-2 ring-blue-500 scale-105' : '') }}">

                @if($isCurrentPlan)
                <div class="absolute top-0 right-0 bg-green-500 text-white px-4 py-1 rounded-bl-lg font-semibold z-10">Ваш тариф</div>
                @elseif($plan['highlighted'])
                <div class="absolute top-0 right-0 bg-blue-500 text-white px-4 py-1 rounded-bl-lg font-semibold z-10">Популярный</div>
                @endif

                <div class="p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan['name'] }}</h3>

                    <div class="mb-6">
                        @if($plan['price'] == 0)
                        <span class="text-4xl font-bold text-gray-900">Бесплатно</span>
                        <span class="text-gray-600">/{{ $plan['period'] }}</span>
                        @else
                            @if(!empty($plan['old_price']) && $plan['old_price'] > $plan['price'])
                                <div class="flex items-baseline space-x-2 flex-wrap">
                                    <span class="text-4xl font-bold text-red-600">{{ $plan['price'] }}</span>
                                    <span class="text-xl text-gray-400 line-through">{{ $plan['old_price'] }}</span>
                                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">-{{ round((1 - $plan['price'] / $plan['old_price']) * 100) }}%</span>
                                </div>
                                <span class="text-gray-600">{{ $plan['currency'] }}/{{ $plan['period'] }}</span>
                            @else
                                <span class="text-4xl font-bold text-gray-900">{{ $plan['price'] }}</span>
                                <span class="text-gray-600">{{ $plan['currency'] }}/{{ $plan['period'] }}</span>
                            @endif
                        @endif
                    </div>

                    <ul class="space-y-3 mb-6">
                        @foreach($plan['features'] as $feature)
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>

                    @if(count($plan['limitations']) > 0)
                    <div class="border-t border-gray-200 pt-4 mb-6">
                        <ul class="space-y-2">
                            @foreach($plan['limitations'] as $limitation)
                            <li class="flex items-start text-sm text-gray-500">
                                <svg class="w-4 h-4 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                {{ $limitation }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @auth
                        @if($isCurrentPlan)
                            <button type="button" disabled class="w-full py-3 px-6 rounded-lg font-semibold bg-green-100 text-green-700 border-2 border-green-500 cursor-not-allowed">
                                Ваш текущий тариф
                            </button>
                        @elseif($isDowngrade)
                            <button type="button" disabled class="w-full py-3 px-6 rounded-lg font-semibold bg-gray-100 text-gray-400 cursor-not-allowed">
                                Недоступно
                            </button>
                        @else
                            <form action="{{ route('pricing.select', $planKey) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <input type="text" name="promo_code" placeholder="Промокод" autocomplete="off"
                                           class="promo-input w-full px-3 py-2 border border-gray-300 rounded-lg text-center font-mono uppercase focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           data-price="{{ $plan['price'] }}">
                                    <div class="promo-status text-xs mt-1 text-center h-4"></div>
                                </div>
                                <button type="submit" class="w-full py-3 px-6 rounded-lg font-semibold transition-colors {{ $isUpgrade ? 'bg-green-500 text-white hover:bg-green-600' : ($plan['highlighted'] ? 'bg-blue-500 text-white hover:bg-blue-600' : 'bg-gray-100 text-gray-900 hover:bg-gray-200') }}">
                                    @if($isUpgrade) Улучшить тариф @else {{ $plan['buttonText'] }} @endif
                                </button>
                            </form>
                        @endif
                    @else
                    <a href="{{ route('register') }}" class="block w-full py-3 px-6 rounded-lg font-semibold text-center transition-colors {{ $plan['highlighted'] ? 'bg-blue-500 text-white hover:bg-blue-600' : 'bg-gray-100 text-gray-900 hover:bg-gray-200' }}">
                        {{ $plan['buttonText'] }}
                    </a>
                    @endauth
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-16 text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Вопросы?</h2>
            <p class="text-gray-600 mb-6">Свяжитесь с нами для получения дополнительной информации о тарифах</p>
            <a href="mailto:support@neiro-jurist.ru" class="text-blue-500 hover:text-blue-600 font-semibold">support@neiro-jurist.ru</a>
        </div>
    </div>
</div>

@if(session('info'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
     class="fixed bottom-4 right-4 bg-blue-500 text-white px-6 py-4 rounded-lg shadow-lg max-w-md">
    <div class="flex items-center justify-between">
        <span>{{ session('info') }}</span>
        <button @click="show = false" class="ml-4 text-white hover:text-gray-200">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.promo-input').forEach(function(input) {
        let timer = null;
        const status = input.parentElement.querySelector('.promo-status');
        const price = parseInt(input.dataset.price);

        input.addEventListener('input', function() {
            clearTimeout(timer);
            const code = this.value.trim().toUpperCase();
            status.textContent = '';
            status.className = 'promo-status text-xs mt-1 text-center h-4';

            if (code.length < 3) return;

            timer = setTimeout(function() {
                fetch('/promo/check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({code: code})
                })
                .then(r => r.json())
                .then(data => {
                    if (data.valid) {
                        status.textContent = '✓ ' + data.message;
                        status.className = 'promo-status text-xs mt-1 text-center h-4 text-green-600 font-semibold';
                        input.classList.remove('border-red-300');
                        input.classList.add('border-green-400');
                    } else {
                        status.textContent = '✗ ' + data.message;
                        status.className = 'promo-status text-xs mt-1 text-center h-4 text-red-500';
                        input.classList.remove('border-green-400');
                        input.classList.add('border-red-300');
                    }
                });
            }, 400);
        });
    });
});
</script>
@endsection