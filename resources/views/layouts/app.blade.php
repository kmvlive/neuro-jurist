<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Нейро-юрист'))</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <meta name="theme-color" content="#1e40af">
    <meta name="color-scheme" content="light dark">

    {!! \App\Models\Setting::get('counter_code') !!}

    <script>
        (function() {
            let saved = localStorage.getItem('theme');
            if (!saved) {
                saved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                localStorage.setItem('theme', saved);
            }
            if (saved === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#3b82f6',
                    }
                }
            }
        }
    </script>

    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col transition-colors">
    <header class="bg-white dark:bg-gray-800 shadow-sm transition-colors">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-xl font-bold text-primary dark:text-blue-400">
                        ⚖️ Нейро-юрист
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <button id="theme-toggle" onclick="toggleTheme()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" title="Сменить тему">
                        <span class="dark:hidden">🌙</span>
                        <span class="hidden dark:inline">☀️</span>
                    </button>

                    <a href="{{ route('templates.index') }}" class="hidden sm:inline-block text-gray-700 dark:text-gray-200 hover:text-primary dark:hover:text-blue-400">
                        📄 Шаблоны
                    </a>

                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-700 dark:text-gray-200 hover:text-primary dark:hover:text-blue-400">
                                Админ-панель
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="text-gray-700 dark:text-gray-200 hover:text-primary dark:hover:text-blue-400">
                                Личный кабинет
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-700 dark:text-gray-200 hover:text-primary dark:hover:text-blue-400">
                                Выход
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-200 hover:text-primary dark:hover:text-blue-400">
                            Вход
                        </a>
                        <a href="{{ route('register') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Регистрация
                        </a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    @if(isset($subscriptionDaysLeft) && $subscriptionDaysLeft !== null)
        @if($subscriptionDaysLeft === 0)
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold">⚠️ Подписка истекла</p>
                <p class="text-sm">Продлите подписку, чтобы продолжить использовать все функции сервиса.</p>
            </div>
        @elseif($subscriptionDaysLeft <= 7)
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                <p class="font-bold">⏰ Подписка заканчивается через {{ $subscriptionDaysLeft }} {{ $subscriptionDaysLeft === 1 ? 'день' : ($subscriptionDaysLeft < 5 ? 'дня' : 'дней') }}</p>
                <p class="text-sm">Продлите подписку, чтобы не потерять доступ к функциям сервиса.</p>
            </div>
        @endif
    @endif

    <main class="flex-grow">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-green-100 dark:bg-green-900/40 border border-green-400 text-green-800 dark:text-green-200 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-red-100 dark:bg-red-900/40 border border-red-400 text-red-800 dark:text-red-200 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-gray-100 dark:bg-black text-gray-800 dark:text-white py-8 mt-auto transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            @php $footerLinks = \App\Models\FooterLink::getActiveLinks(); @endphp
            @if($footerLinks->count() > 0)
                <nav class="mb-4 flex flex-wrap justify-center gap-x-6 gap-y-2">
                    @foreach($footerLinks as $link)
                        <a href="{{ $link->url }}" 
                           {{ $link->is_external ? 'target="_blank" rel="noopener"' : '' }}
                           class="text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-blue-400 text-sm hover:underline">
                            {{ $link->title }}
                        </a>
                    @endforeach
                </nav>
            @endif
            <p>&copy; {{ date('Y') }} Нейро-юрист. Все права защищены.</p>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">AI-ассистент для юридических задач</p>
        </div>
    </footer>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }
    </script>

    @stack('scripts')
</body>
</html>
