@extends('layouts.app')

@section('title', ($prompt ? 'Редактировать' : 'Создать') . ' промпт — Админ-панель')

@section('content')
<div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if($prompt)
    @php
        $fbUp = App\Models\MessageFeedback::whereHas('message.chat', fn($q) => $q->where('prompt_key', $prompt->key))->where('vote', 1)->count();
        $fbDown = App\Models\MessageFeedback::whereHas('message.chat', fn($q) => $q->where('prompt_key', $prompt->key))->where('vote', -1)->count();
        $fbTotal = $fbUp + $fbDown;
        $fbRate = $fbTotal > 0 ? round($fbUp / $fbTotal * 100) : null;
    @endphp
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">📊 Отзывы по теме</h3>
            @if($fbRate !== null)
                <span class="text-xs font-bold px-2 py-1 rounded {{ $fbRate >= 70 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($fbRate >= 50 ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400') }}">
                    {{ $fbRate }}% 👍
                </span>
            @endif
        </div>
        <div class="flex gap-4 text-sm">
            <div class="flex items-center gap-1">
                <span class="text-green-600">👍</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $fbUp }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">полезных</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="text-red-600">👎</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $fbDown }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">плохих</span>
            </div>
        </div>
        @if($fbDown > 0)
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                💡 Используйте кнопку "🤖 Улучшить промпт" ниже, чтобы AI проанализировал плохие отзывы и предложил улучшения
            </p>
        @endif
    </div>
    @endif

    <a href="{{ route('admin.quick-prompts.index') }}" class="text-primary hover:underline">← К промптам</a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-4 mb-6">{{ $prompt ? 'Редактировать промпт' : 'Новый промпт' }}</h1>

    <form method="POST" action="{{ $prompt ? route('admin.quick-prompts.update', $prompt) : route('admin.quick-prompts.store') }}" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-4">
        @csrf
        @if($prompt) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Иконка (эмодзи)</label>
            <input type="text" name="icon" value="{{ old('icon', $prompt?->icon ?? '📄') }}" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-3 py-2 text-2xl text-center" maxlength="10">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Название</label>
            <input type="text" name="title" value="{{ old('title', $prompt?->title) }}" required class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-3 py-2" placeholder="Претензия в магазин">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ключ (латиница, для кода)</label>
            <input type="text" name="key" value="{{ old('key', $prompt?->key) }}" required pattern="[a-z0-9_]+" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-3 py-2 font-mono" placeholder="claim_shop">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Только строчные буквы, цифры и подчёркивания</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст промта (инструкция для AI)</label>
            <textarea name="text" rows="6" class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 dark:bg-gray-700 dark:text-white" placeholder="Действуй как опытный юрист по авторскому праву. Пользователь - фотограф, чьи работы использовали без разрешения. Помоги составить претензию...">{{ old('text', $prompt?->text) }}</textarea>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Этот текст отправится как первое сообщение пользователя при клике на промпт. Оставь пустым - отправится только название темы.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Разделы в каталоге (можно выбрать несколько)</label>
            <div class="border border-gray-300 dark:border-gray-600 rounded p-3 max-h-60 overflow-y-auto bg-gray-50 dark:bg-gray-900 space-y-1">
                @foreach($categories as $cat)
                    <label class="flex items-center gap-2 py-1 px-2 hover:bg-white dark:bg-gray-800 rounded cursor-pointer">
                        <input type="checkbox" name="categories[]" value="{{ $cat->id }}" 
                            @checked(in_array($cat->id, old('categories', $prompt ? $prompt->categories->pluck('id')->toArray() : [])))
                            class="rounded">
                        <span>
                            {{ $cat->parent ? '└─ ' : '' }}{{ $cat->icon }} {{ $cat->name }}
                        </span>
                    </label>
                @endforeach
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Управление разделами: <a href="{{ route('admin.prompt-categories.index') }}" class="text-primary hover:underline">Категории промтов →</a>
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Порядок сортировки</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $prompt?->sort_order ?? 0) }}" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-3 py-2">
        </div>

        <label class="flex items-center gap-2">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" {{ old('active', $prompt?->active ?? true) ? 'checked' : '' }} class="rounded">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Активен</span>
        </label>

        <label class="flex items-center gap-2">
            <input type="hidden" name="show_in_chat" value="0">
            <input type="checkbox" name="show_in_chat" value="1" {{ old('show_in_chat', $prompt?->show_in_chat ?? true) ? 'checked' : '' }} class="rounded">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Показывать в чате (на главной)</span>
        </label>

        @if($prompt)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">🔍 SEO для лендинга /consult/{{ $prompt->key }}</h2>
                <form method="POST" action="{{ route('admin.quick-prompts.generate-seo', $prompt) }}">
                    @csrf
                    <div class="flex gap-2">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg">✨ Сгенерировать SEO</button>
                    <button type="button" id="improve-prompt-btn" class="bg-orange-600 hover:bg-orange-700 text-white text-sm px-4 py-2 rounded-lg">🤖 Улучшить промпт</button>
                </div>
                </form>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SEO-заголовок</label>
                    <input type="text" name="seo_title" value="{{ old('seo_title', $prompt->seo_title) }}" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-3 py-2 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Мета-описание</label>
                    <textarea name="seo_description" rows="2" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-3 py-2 dark:text-white">{{ old('seo_description', $prompt->seo_description) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст страницы</label>
                    <textarea name="seo_text" rows="5" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-3 py-2 dark:text-white">{{ old('seo_text', $prompt->seo_text) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Частые вопросы (по одному в строке)</label>
                    <textarea name="example_questions" rows="4" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded px-3 py-2 dark:text-white">{{ old('example_questions', implode("\n", $prompt->example_questions ?? [])) }}</textarea>
                </div>
                <a href="{{ route('consult.show', $prompt->key) }}" target="_blank" class="text-sm text-primary dark:text-blue-400 hover:underline">👁 Открыть лендинг /consult/{{ $prompt->key }}</a>
            </div>
        </div>
        @endif
        <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:bg-blue-700 font-medium">Сохранить</button>
    </form>

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

</div>

@push('scripts')
<script>
(function() {
    const promptId = @json($prompt?->id);
    if (!promptId) return;

    const improveBtn = document.getElementById('improve-prompt-btn');
    const modal = document.getElementById('improve-prompt-modal');
    const loading = document.getElementById('improve-loading');
    const result = document.getElementById('improve-result');
    const error = document.getElementById('improve-error');
    
    if (!improveBtn) return;

    // Открытие модалки и запуск улучшения
    improveBtn.addEventListener('click', async () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        loading.classList.remove('hidden');
        result.classList.add('hidden');
        error.classList.add('hidden');

        try {
            const response = await fetch(`/admin/quick-prompts/${promptId}/improve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            loading.classList.add('hidden');

            if (!data.success) {
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

            // Обработчик кнопки "Применить"
            document.getElementById('improve-apply-btn').onclick = async () => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/quick-prompts/${promptId}/apply-improved`;
                
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
                textInput.value = data.improved_text;
                
                form.appendChild(csrfInput);
                form.appendChild(methodInput);
                form.appendChild(textInput);
                document.body.appendChild(form);
                form.submit();
            };

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
})();
</script>
@endpush

@endsection
