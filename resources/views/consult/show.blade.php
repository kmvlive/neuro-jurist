@extends('layouts.app')

@section('title', $seoTitle)

@push('meta')
<meta name="description" content="{{ $seoDescription }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:type" content="article">
<meta property="og:url" content="{{ url()->current() }}">

{{-- schema.org FAQPage микроразметка для красивых сниппетов --}}
@if($prompt->example_questions && $prompt->example_answers)
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        @foreach($prompt->example_questions as $i => $q)
        {
            "@type": "Question",
            "name": "{{ $q }}",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "{{ $prompt->example_answers[$i] ?? '' }}"
            }
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endif

{{-- BreadcrumbList + Service: Google показывает крошки в выдаче --}}
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@graph": [
        {
            "@type": "BreadcrumbList",
            "itemListElement": [
                {"@type": "ListItem", "position": 1, "name": "Главная", "item": "{{ url('/') }}"},
                {"@type": "ListItem", "position": 2, "name": "Консультации", "item": "{{ route('consult.index') }}"},
                {"@type": "ListItem", "position": 3, "name": "{{ $prompt->title }}", "item": "{{ url()->current() }}"}
            ]
        },
        {
            "@type": "Service",
            "name": "{{ $prompt->seo_title ?: $prompt->title }}",
            "description": "{{ $seoDescription }}",
            "serviceType": "Юридическая консультация",
            "areaServed": "RU",
            "provider": {
                "@type": "Organization",
                "name": "Нейро-юрист",
                "url": "{{ url('/') }}"
            },
            "offers": {"@type": "Offer", "price": "0", "priceCurrency": "RUB"}
        }
    ]
}
</script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Хлебные крошки --}}
    <nav class="text-sm text-gray-500 dark:text-gray-400 mb-6">
        <a href="{{ route('home') }}" class="hover:text-primary">Главная</a>
        <span class="mx-2">›</span>
        <a href="{{ route('consult.index') }}" class="hover:text-primary">Консультации</a>
        <span class="mx-2">›</span>
        <span class="text-gray-900 dark:text-white">{{ $prompt->title }}</span>
    </nav>

    {{-- H1 --}}
    <div class="mb-8">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
            {{ $prompt->icon }} {{ $prompt->seo_title ?: $prompt->title }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-3">{{ $seoDescription }}</p>
    </div>

    {{-- SEO-текст --}}
    @if($prompt->seo_text)
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
            <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300 whitespace-pre-line">
                {{ $prompt->seo_text }}
            </div>
        </div>
    @endif

    {{-- CTA --}}
    <div class="bg-gradient-to-r from-primary to-blue-600 rounded-xl p-6 mb-8 text-center">
        <h2 class="text-xl font-bold text-white mb-2">Получите бесплатную консультацию</h2>
        <p class="text-blue-100 mb-4">AI-юрист ответит на ваш вопрос по теме «{{ $prompt->title }}» за пару минут</p>
        <a href="{{ route('chat.show', ['topic' => $prompt->key]) }}"
           class="inline-block bg-white text-primary font-bold px-8 py-3 rounded-lg hover:bg-blue-50 transition">
            💬 Задать вопрос юристу
        </a>
        <p class="text-blue-200 text-sm mt-3">Бесплатно: {{ \App\Models\Setting::get('free_messages', 20) }} сообщений без регистрации</p>
    </div>

    {{-- Частые вопросы --}}
    @if($prompt->example_questions)
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">❓ Частые вопросы</h2>
            <div class="space-y-3">
                @foreach($prompt->example_questions as $i => $q)
                    <details class="group bg-gray-50 dark:bg-gray-700 rounded-lg overflow-hidden">
                        <summary class="flex items-center justify-between p-4 cursor-pointer text-gray-800 dark:text-gray-100 font-medium hover:bg-gray-100 dark:hover:bg-gray-600 transition list-none">
                            <span>{{ $q }}</span>
                            <span class="text-primary dark:text-blue-400 group-open:rotate-45 transition-transform text-xl leading-none">+</span>
                        </summary>
                        <div class="px-4 pb-3 text-sm text-gray-600 dark:text-gray-300">
                            <p class="mb-3">{{ $prompt->example_answers[$i] ?? '' }}</p>
                            <a href="{{ route('chat.show', ['topic' => $prompt->key, 'q' => $q]) }}"
                               class="inline-block text-primary dark:text-blue-400 font-medium hover:underline">
                                💬 Задать этот вопрос AI-юристу →
                            </a>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Связанные темы --}}
    @if($relatedPrompts->count())
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">📚 Похожие консультации</h2>
            <div class="grid sm:grid-cols-3 gap-4">
                @foreach($relatedPrompts as $rel)
                    <a href="{{ route('consult.show', $rel->key) }}"
                       class="block bg-white dark:bg-gray-800 shadow rounded-lg p-4 hover:shadow-md transition">
                        <div class="text-2xl mb-2">{{ $rel->icon }}</div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $rel->title }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
