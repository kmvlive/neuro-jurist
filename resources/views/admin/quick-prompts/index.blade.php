@extends('layouts.app')

@section('title', 'Quick-промпты — Админ-панель')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">🎯 Quick-промпты и реклама</h1>
        <div class="flex gap-2 flex-wrap">
            <form method="POST" action="{{ route('admin.quick-prompts.toggle-all-ads') }}" class="inline">
                @csrf
                <input type="hidden" name="action" value="enable">
                <button type="submit" class="bg-green-500 text-white px-4 py-3 rounded-lg hover:bg-green-600 text-sm font-medium">✓ Включить всю рекламу</button>
            </form>
            <form method="POST" action="{{ route('admin.quick-prompts.toggle-all-ads') }}" class="inline">
                @csrf
                <input type="hidden" name="action" value="disable">
                <button type="submit" onclick="return confirm('Отключить всю рекламу?')" class="bg-red-500 text-white px-4 py-3 rounded-lg hover:bg-red-600 text-sm font-medium">✕ Отключить всю рекламу</button>
            </form>
            <a href="{{ route('admin.prompt-categories.index') }}" class="bg-indigo-500 text-white px-4 py-3 rounded-lg hover:bg-indigo-600 text-sm font-medium">📚 Категории</a>
            <a href="{{ route('admin.quick-prompts.create') }}" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 text-center font-medium">+ Создать промпт</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 text-sm text-yellow-800">
        <strong>Как работает:</strong> Незарегистрированные пользователи видят рекламу между 14 и 15 сообщением. Привяжите рекламу к каждому промпту ниже.
    </div>

    <div class="space-y-3">
        @forelse($prompts as $p)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <span class="text-2xl">{{ $p->icon }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $p->title }}</span>
                            <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded font-mono">{{ $p->key }}</span>
                            @if($p->categories->count() > 0)
                                @foreach($p->categories as $cat)
                                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">
                                        {{ $cat->icon }} {{ $cat->parent ? $cat->parent->name . ' → ' : '' }}{{ $cat->name }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2 py-1 rounded">Без категории</span>
                            @endif
                            @if(!$p->active)
                                <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded">Неактивен</span>
                            @endif
                            @if(!$p->show_in_chat)
                                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded">🔒 Только в каталоге</span>
                            @else
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">💬 В чате</span>
                            @endif
                        </div>
                        <div class="text-sm mb-2 flex gap-4">
                            <div>
                                @if($p->ad && $p->ad->active)
                                    <span class="text-green-600">✓ Реклама настроена</span>
                                @else
                                    <span class="text-orange-600">⚠ Реклама не настроена</span>
                                @endif
                            </div>
                            @if(isset($feedbackStats[$p->key]))
                                @php
                                    $stats = $feedbackStats[$p->key];
                                    $rate = $stats->total > 0 ? round($stats->up / $stats->total * 100) : null;
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="text-green-600">👍 {{ $stats->up }}</span>
                                    <span class="text-red-600">👎 {{ $stats->down }}</span>
                                    @if($rate !== null)
                                        <span class="text-xs font-bold px-2 py-0.5 rounded {{ $rate >= 70 ? "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400" : ($rate >= 50 ? "bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400" : "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400") }}">
                                            {{ $rate }}%
                                        </span>
                                    @endif
                                </div>
                            @else
                                <div class="text-gray-400 dark:text-gray-500">Нет отзывов</div>
                            @endif
                        </div>
                        <div class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 dark:text-gray-300">
                            <span>{{ $p->icon }}</span>
                            <span>{{ $p->title }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0 flex-wrap">
                        <a href="{{ route('admin.quick-prompts.edit', $p) }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:bg-gray-600 text-sm">Ред.</a>
                        <button type="button" class="improve-prompt-btn px-4 py-2 bg-orange-100 text-orange-700 rounded-lg hover:bg-orange-200 text-sm" data-prompt-id="{{ $p->id }}" data-prompt-title="{{ $p->title }}">🤖 Улучшить</button>
                        <a href="{{ route('admin.quick-prompts.ad.edit', $p) }}" class="px-4 py-2 bg-orange-100 text-orange-700 rounded-lg hover:bg-orange-200 text-sm font-medium">📢 Реклама</a>
                        <form method="POST" action="{{ route('admin.quick-prompts.destroy', $p) }}" onsubmit="return confirm('Удалить?')">
                            @csrf @method('DELETE')
                            <button class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 text-sm">Удалить</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center text-gray-500 dark:text-gray-400">Промптов нет</div>
        @endforelse
    </div>
</div>

{{-- Модальное окно улучшения промпта --}}
<div id="improve-prompt-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">🤖 Улучшение промпта через AI</h2>
            <button type="button" id="improve-modal-close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl">&times;</button>
        </div>
        
        <div id="improve-loading" class="text-center py-8 hidden">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600 mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">AI анализирует отзывы и улучшает промпт...</p>
        </div>
        
        <div id="improve-result" class="hidden">
            <div class="mb-4 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xl">✨</span>
                    <span class="font-semibold text-gray-900 dark:text-white">Ключевые изменения</span>
                </div>
                <p id="improve-changes-summary" class="text-sm text-gray-700 dark:text-gray-300 mb-2"></p>
                <div id="improve-issues-addressed" class="flex flex-wrap gap-2 mt-2"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Текущий промпт</h3>
                    <div id="improve-current-text" class="bg-gray-50 dark:bg-gray-700/50 rounded p-3 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap max-h-96 overflow-y-auto border border-gray-200 dark:border-gray-600"></div>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">✨ Улучшенный промпт</h3>
                    <div id="improve-new-text" class="bg-green-50 dark:bg-green-900/20 rounded p-3 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap max-h-96 overflow-y-auto border border-green-200 dark:border-green-800"></div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" id="improve-apply-btn" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-medium">
                    ✅ Применить улучшенный промпт
                </button>
                <button type="button" id="improve-cancel-btn" class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 py-3 rounded-lg font-medium">
                    ❌ Отмена
                </button>
            </div>
        </div>
        
        <div id="improve-error" class="hidden p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-red-700 dark:text-red-300"></p>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const modal = document.getElementById('improve-prompt-modal');
    const loading = document.getElementById('improve-loading');
    const result = document.getElementById('improve-result');
    const error = document.getElementById('improve-error');
    
    let currentPromptId = null;

    // Обработчик для всех кнопок "Улучшить"
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.improve-prompt-btn');
        if (!btn) return;

        currentPromptId = btn.dataset.promptId;
        const promptTitle = btn.dataset.promptTitle;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        loading.classList.remove('hidden');
        result.classList.add('hidden');
        error.classList.add('hidden');

        try {
            // Получаем CSRF-токен из meta или cookie
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content 
                || document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1];
            
            if (!csrfToken) {
                loading.classList.add('hidden');
                error.classList.remove('hidden');
                error.querySelector('p').textContent = 'CSRF-токен не найден. Обновите страницу.';
                return;
            }

            const response = await fetch(`/admin/quick-prompts/${currentPromptId}/improve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            loading.classList.add('hidden');

            // Проверяем, что ответ - JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Server returned HTML:', text.substring(0, 200));
                error.classList.remove('hidden');
                error.querySelector('p').textContent = 'Сессия истекла. Обновите страницу и попробуйте снова.';
                return;
            }

            const data = await response.json();

            if (!response.ok || !data.success) {
                error.classList.remove('hidden');
                error.querySelector('p').textContent = data.message || 'Ошибка улучшения';
                return;
            }

            // Показываем результат
            result.classList.remove('hidden');
            
            document.getElementById('improve-current-text').textContent = data.current_text || '(пустой)';
            document.getElementById('improve-new-text').textContent = data.improved_text;
            document.getElementById('improve-changes-summary').textContent = data.changes_summary || 'Промпт был улучшен на основе отзывов пользователей';
            
            const issuesContainer = document.getElementById('improve-issues-addressed');
            issuesContainer.innerHTML = '';
            (data.issues_addressed || []).forEach(issue => {
                const badge = document.createElement('span');
                badge.className = 'text-xs bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300 px-2 py-1 rounded';
                badge.textContent = '✓ ' + issue;
                issuesContainer.appendChild(badge);
            });

        } catch (e) {
            loading.classList.add('hidden');
            error.classList.remove('hidden');
            error.querySelector('p').textContent = 'Ошибка сети: ' + e.message;
        }
    });

    // Закрытие модалки
    document.getElementById('improve-modal-close').addEventListener('click', closeModal);
    document.getElementById('improve-cancel-btn').addEventListener('click', closeModal);

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Применить улучшенный промпт
    document.getElementById('improve-apply-btn').addEventListener('click', async () => {
        if (!currentPromptId) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/quick-prompts/${currentPromptId}/apply-improved`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        
        const textInput = document.createElement('input');
        textInput.type = 'hidden';
        textInput.name = 'improved_text';
        textInput.value = document.getElementById('improve-new-text').textContent;
        
        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        form.appendChild(textInput);
        document.body.appendChild(form);
        form.submit();
    });
})();
</script>
@endpush

@endsection
