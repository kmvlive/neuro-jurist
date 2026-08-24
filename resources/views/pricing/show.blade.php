@extends('layouts.app')

@section('title', 'Тарифы')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Заголовок -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Выберите подходящий тариф
            </h1>
            <p class="text-xl text-gray-600">
                Начните с бесплатного тарифа или выберите профессиональный план
            </p>
        </div>

        <!-- Карточки тарифов -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($plans as $index => $plan)
            <div class="relative bg-white rounded-2xl shadow-xl overflow-hidden transform transition-all hover:scale-105 {{ $plan['highlighted'] ? 'ring-2 ring-blue-500 scale-105' : '' }}">
                @if($plan['highlighted'])
                <div class="absolute top-0 right-0 bg-blue-500 text-white px-4 py-1 rounded-bl-lg font-semibold">
                    Популярный
                </div>
                @endif

                <div class="p-8">
                    <!-- Название и цена -->
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan['name'] }}</h3>
                    
                    <div class="mb-6">
                        @if($plan['price'] == 0)
                        <span class="text-4xl font-bold text-gray-900">Бесплатно</span>
                        <span class="text-gray-600">/{{ $plan['period'] }}</span>
                        @else
                        <span class="text-4xl font-bold text-gray-900">{{ $plan['price'] }}</span>
                        <span class="text-gray-600">{{ $plan['currency'] }}/{{ $plan['period'] }}</span>
                        @endif
                    </div>

                    <!-- Преимущества -->
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

                    <!-- Ограничения (для бесплатного тарифа) -->
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

                    <!-- Кнопка -->
                    @auth
                    <form action="{{ route('pricing.select', $plan['name'] === 'Старт' ? 'start' : ($plan['name'] === 'Профи' ? 'profi' : 'business')) }}" method="POST">
                        @csrf
                        <button type="submit" 
                            class="w-full py-3 px-6 rounded-lg font-semibold transition-colors {{ $plan['highlighted'] ? 'bg-blue-500 text-white hover:bg-blue-600' : 'bg-gray-100 text-gray-900 hover:bg-gray-200' }}">
                            {{ $plan['buttonText'] }}
                        </button>
                    </form>
                    @else
                    <a href="{{ route('register') }}" 
                        class="block w-full py-3 px-6 rounded-lg font-semibold text-center transition-colors {{ $plan['highlighted'] ? 'bg-blue-500 text-white hover:bg-blue-600' : 'bg-gray-100 text-gray-900 hover:bg-gray-200' }}">
                        {{ $plan['buttonText'] }}
                    </a>
                    @endauth
                </div>
            </div>
            @endforeach
        </div>

        <!-- Дополнительная информация -->
        <div class="mt-16 text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                Вопросы?
            </h2>
            <p class="text-gray-600 mb-6">
                Свяжитесь с нами для получения дополнительной информации о тарифах
            </p>
            <a href="mailto:support@neiro-jurist.ru" class="text-blue-500 hover:text-blue-600 font-semibold">
                support@neiro-jurist.ru
            </a>
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
@endsection
