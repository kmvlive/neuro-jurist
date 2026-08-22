@extends('layouts.app')

@section('title', 'Нейро-юрист — AI-ассистент для юридических задач')

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-r from-primary to-secondary text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-6">
            ⚖️ Нейро-юрист
        </h1>
        <p class="text-xl md:text-2xl mb-8">
            Ваш персональный AI-ассистент для решения юридических задач
        </p>
        <div class="flex justify-center space-x-4">
            @guest
            <a href="{{ route('chat.show') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Начать бесплатно
            </a>
            <a href="{{ route('login') }}" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-primary transition">
                Войти
            </a>
            @else
            <a href="{{ route('dashboard') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Перейти в кабинет
            </a>
            @endguest
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-12">Возможности сервиса</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center p-6">
                <div class="text-4xl mb-4">💬</div>
                <h3 class="text-xl font-semibold mb-2">Юридические консультации</h3>
                <p class="text-gray-600">Получайте ответы на юридические вопросы от AI-ассистента 24/7</p>
            </div>
            <div class="text-center p-6">
                <div class="text-4xl mb-4">📄</div>
                <h3 class="text-xl font-semibold mb-2">Анализ документов</h3>
                <p class="text-gray-600">Проверка договоров, претензий и других документов на соответствие законодательству</p>
            </div>
            <div class="text-center p-6">
                <div class="text-4xl mb-4">⚡</div>
                <h3 class="text-xl font-semibold mb-2">Быстро и удобно</h3>
                <p class="text-gray-600">Мгновенные ответы без ожидания записи к юристу</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-12">Тарифы</h2>
        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <h3 class="text-xl font-semibold mb-2">Старт</h3>
                <p class="text-3xl font-bold text-primary mb-4">Бесплатно</p>
                <ul class="text-gray-600 mb-6 space-y-2">
                    <li>• 20 консультаций</li>
                    <li>• Базовый анализ документов</li>
                    <li>• Поддержка по email</li>
                </ul>
                <a href="{{ route('chat.show') }}" class="block bg-primary text-white py-2 rounded-lg hover:bg-blue-700">
                    Попробовать
                </a>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center border-2 border-primary transform scale-105">
                <div class="bg-primary text-white text-sm py-1 px-3 rounded-full inline-block mb-4">Популярный</div>
                <h3 class="text-xl font-semibold mb-2">Профи</h3>
                <p class="text-3xl font-bold text-primary mb-4">990 ₽/мес</p>
                <ul class="text-gray-600 mb-6 space-y-2">
                    <li>• Безлимитные консультации</li>
                    <li>• Глубокий анализ документов</li>
                    <li>• Приоритетная поддержка</li>
                    <li>• История чатов</li>
                </ul>
                <a href="{{ route('register') }}" class="block bg-primary text-white py-2 rounded-lg hover:bg-blue-700">
                    Выбрать тариф
                </a>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <h3 class="text-xl font-semibold mb-2">Бизнес</h3>
                <p class="text-3xl font-bold text-primary mb-4">2990 ₽/мес</p>
                <ul class="text-gray-600 mb-6 space-y-2">
                    <li>• Всё из тарифа Профи</li>
                    <li>• До 5 пользователей</li>
                    <li>• API доступ</li>
                    <li>• Персональный менеджер</li>
                </ul>
                <a href="{{ route('register') }}" class="block bg-primary text-white py-2 rounded-lg hover:bg-blue-700">
                    Связаться с нами
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
