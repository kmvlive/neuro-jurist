@extends('layouts.app')

@section('title', 'Каталог консультаций')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    {{-- Заголовок --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">📚 Каталог консультаций</h1>
        <p class="text-gray-600 dark:text-gray-400">Всего доступных тем: <span class="font-semibold">{{ $totalPrompts }}</span></p>
    </div>

    {{-- Разделы --}}
    <div class="space-y-8">
        @foreach($sections as $section)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                {{-- Заголовок раздела --}}
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-750 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        <span class="mr-2">{{ $section->icon }}</span>{{ $section->name }}
                    </h2>
                </div>

                <div class="p-6">
                    {{-- Промты без подраздела --}}
                    @if($section->quickPrompts->count() > 0)
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                            @foreach($section->quickPrompts as $prompt)
                                @include('prompts._card', ['prompt' => $prompt])
                            @endforeach
                        </div>
                    @endif

                    {{-- Подразделы --}}
                    @foreach($section->children as $sub)
                        @if($sub->quickPrompts->count() > 0)
                            <div class="mb-6 last:mb-0">
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                                    {{ $sub->name }}
                                </h3>
                                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($sub->quickPrompts as $prompt)
                                        @include('prompts._card', ['prompt' => $prompt])
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Кнопка назад --}}
    <div class="mt-8 text-center">
        <a href="{{ route('chat.show') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition">
            ← Вернуться к чату
        </a>
    </div>
</div>
@endsection

cat > /var/www/neuro-jurist/resources/views/prompts/_card.blade.php << 'EOF'
<a href="{{ route('chat.show', ['prompt' => $prompt->key]) }}" 
   class="group block p-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg hover:border-blue-300 dark:hover:border-blue-500 hover:shadow-md transition">
    <div class="flex items-start gap-3">
        <span class="text-2xl flex-shrink-0">{{ $prompt->icon }}</span>
        <div class="flex-1 min-w-0">
            <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                {{ $prompt->title }}
            </h4>
            @if($prompt->text)
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                    {{ Str::limit($prompt->text, 80) }}
                </p>
            @endif
        </div>
        <span class="text-gray-400 group-hover:text-blue-500 transition flex-shrink-0">→</span>
    </div>
</a>
