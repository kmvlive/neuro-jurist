@extends('layouts.app')

@section('title', 'Нейро-юрист — AI-ассистент')

@push('styles')
<style>
    /* Sidebar: мобильный и десктоп */
    #chat-sidebar {
        position: fixed;
        top: 64px;
        left: 0;
        z-index: 50;
        height: calc(100vh - 64px);
        height: calc(100dvh - 64px);
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
    }
    #chat-sidebar.sidebar-open { transform: translateX(0); }
    @media (min-width: 768px) {
        #chat-sidebar {
            position: relative;
            top: 0;
            left: auto;
            z-index: auto;
            height: 100%;
            transform: none;
            transition: none;
        }
    }

    .markdown-body { line-height: 1.6; word-wrap: break-word; }
    .markdown-body p { margin-bottom: 0.75rem; }
    .markdown-body p:last-child { margin-bottom: 0; }
    .markdown-body strong { font-weight: 700; }
    .markdown-body em { font-style: italic; }
    .markdown-body h1, .markdown-body h2, .markdown-body h3, .markdown-body h4 {
        font-weight: 700; margin-top: 1rem; margin-bottom: 0.5rem; line-height: 1.3;
    }
    .markdown-body h1 { font-size: 1.25rem; }
    .markdown-body h2 { font-size: 1.15rem; }
    .markdown-body h3 { font-size: 1.05rem; }
    .markdown-body ul, .markdown-body ol { margin: 0.5rem 0; padding-left: 1.5rem; }
    .markdown-body ul { list-style-type: disc; }
    .markdown-body ol { list-style-type: decimal; }
    .markdown-body li { margin-bottom: 0.25rem; }
    .markdown-body li p { margin-bottom: 0.25rem; }
    .markdown-body code {
        background: rgba(0,0,0,0.08); padding: 0.15rem 0.35rem; border-radius: 0.25rem;
        font-size: 0.85em; font-family: 'Courier New', monospace;
    }
    .dark .markdown-body code { background: rgba(255,255,255,0.1); }
    .markdown-body pre {
        background: rgba(0,0,0,0.05); padding: 0.75rem; border-radius: 0.5rem;
        overflow-x: auto; margin: 0.5rem 0;
    }
    .dark .markdown-body pre { background: rgba(0,0,0,0.3); }
    .markdown-body pre code { background: transparent; padding: 0; }
    .markdown-body blockquote {
        border-left: 3px solid currentColor; padding-left: 0.75rem; margin: 0.5rem 0; opacity: 0.85;
    }
    .markdown-body a { color: #2563eb; text-decoration: underline; }
    .dark .markdown-body a { color: #60a5fa; }
    .markdown-body hr { border: none; border-top: 1px solid currentColor; opacity: 0.3; margin: 0.75rem 0; }
    .mic-active { background: #ef4444 !important; color: #fff !important; animation: pulse 1.2s infinite; }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.6; } }
    .quick-scroll { scrollbar-width: none; -ms-overflow-style: none; }
    .quick-scroll::-webkit-scrollbar { display: none; }
    mark.search-mark { background: #fde047; color: #1f2937; padding: 0 2px; border-radius: 2px; }
    mark.search-mark.search-current { background: #fb923c; color: #fff; }

</style>
@endpush

@section('content')
<div id="chat-page" class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-2 sm:py-4 flex gap-4 relative"
     style="height: calc(100vh - 72px); height: calc(100dvh - 72px); max-height: 85vh;">
    
    <div id="mobile-overlay" class="md:hidden fixed inset-0 bg-black/50 z-40 hidden" style="top: 64px;"></div>

    <!-- Sidebar -->
    <div id="chat-sidebar" 
         class="w-72 bg-white dark:bg-gray-800 rounded-lg shadow flex flex-col flex-shrink-0">
        
        <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <form method="POST" action="{{ route('chat.create') }}" class="flex-1 mr-2">
                @csrf
                <button type="submit" class="w-full bg-primary hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Новый чат</span>
                </button>
            </form>
            <button id="mobile-menu-close" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="p-2 border-b border-gray-200 dark:border-gray-700">
            <input type="text" id="global-chat-search" placeholder="🔍 Поиск по всем чатам..."
                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-800 dark:text-gray-100"
                   autocomplete="off">
            <div id="global-search-results" class="hidden mt-2 space-y-1 max-h-64 overflow-y-auto"></div>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1">
            @forelse($chats as $chat)
                <a href="{{ route('chat.show', ['chat_id' => $chat->id]) }}" 
                   class="chat-link block px-3 py-2 rounded-md text-sm {{ $chatId == $chat->id ? 'bg-blue-100 dark:bg-blue-900/50 text-primary dark:text-blue-300 font-medium' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }} flex items-center justify-between group">
                    <span class="truncate flex-1">
                        @php
                            $categoryIcons = [
                                'labor' => '⚖️',
                                'family' => '👨‍👩‍👧',
                                'housing' => '🏠',
                                'consumer' => '📝',
                                'traffic' => '🚗',
                                'court' => '🏛️',
                                'other' => '💼',
                            ];
                            $icon = $chat->category ? ($categoryIcons[$chat->category] ?? '💼') : '';
                        @endphp
                        @if($icon)<span class="mr-1">{{ $icon }}</span>@endif
                        {{ $chat->summary ?: ($chat->title ?: 'Новый чат') }}
                    </span>
                    @if($chatId == $chat->id)
                        <form method="POST" action="{{ route('chat.destroy', $chat->id) }}" 
                              onsubmit="return confirm('Удалить этот чат?')" class="ml-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                                </svg>
                            </button>
                        </form>
                    @endif
                </a>
            @empty
                <p class="text-center text-gray-500 dark:text-gray-400 text-sm py-4">Нет чатов</p>
            @endforelse
        </div>
        
        <div class="p-3 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
            @if(!$isGuest && (auth()->user()->hasUnlimitedMessages() || auth()->user()->hasActiveSubscription()))
                Сообщения: безлимит ✨
            @else
                Бесплатно: {{ $remainingMessages }} из {{ $freeLimit }} сообщений.
            @endif
            @if($isGuest)
                · <a href="{{ route('register') }}" class="text-primary dark:text-blue-400 hover:underline">Регистрация</a>
            @endif
        </div>
    </div>

    <!-- Основная область чата -->
    <div class="flex-1 bg-white dark:bg-gray-800 rounded-lg shadow flex flex-col min-w-0">
        
        <div class="flex items-center gap-3 px-3 py-2 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
            <button id="mobile-menu-toggle" class="md:hidden p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600">
                <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="font-medium text-sm truncate text-gray-700 dark:text-gray-200 flex-1">
                {{ $currentChat->title ?? 'Новый чат' }}
            </span>
            @if(!$isGuest && (auth()->user()->hasUnlimitedMessages() || auth()->user()->hasActiveSubscription()))
                <span class="hidden sm:inline text-xs text-gray-500 dark:text-gray-400">✨ Безлимит</span>
            @else
                <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">
                    <span class="hidden sm:inline">Бесплатно:</span> {{ $remainingMessages }}/{{ $freeLimit }}
                </span>
            @endif
            <button type="button" id="search-toggle"
                    class="flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex-shrink-0"
                    title="Поиск по чату">
                🔍
            </button>
            <button type="button" id="voice-toggle"
                    class="flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex-shrink-0"
                    title="Озвучивать ответы AI">
                🔊
            </button>
        </div>
        <div id="search-panel" class="hidden px-3 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex items-center gap-2 flex-shrink-0">
            <input type="text" id="search-input" placeholder="Поиск по чату..." class="flex-1 min-w-0 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-800 dark:text-gray-100">
            <span id="search-count" class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">0/0</span>
            <button type="button" id="search-prev" class="px-2 py-1 rounded border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" title="Предыдущее">↑</button>
            <button type="button" id="search-next" class="px-2 py-1 rounded border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" title="Следующее">↓</button>
            <button type="button" id="search-close" class="px-2 py-1 rounded text-gray-500 hover:text-red-500 text-sm" title="Закрыть поиск">✕</button>
        </div>

        @if(session('error'))
            <div class="bg-red-100 dark:bg-red-900/40 border border-red-400 text-red-800 dark:text-red-200 px-4 py-3 m-4 rounded relative">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex-1 p-3 sm:p-4 overflow-y-auto space-y-4 min-h-0" id="messages-container">
            @if($currentChat && $messages->count() > 0)
                @foreach($messages as $msg)
                    <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] sm:max-w-[80%] rounded-lg px-4 py-2 {{ $msg->role === 'user' ? 'bg-primary text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100' }}">
                            @if($msg->role === 'assistant' && !empty($msg->is_ad))
                                {{-- РЕКЛАМНОЕ СООБЩЕНИЕ --}}
                                <div class="ad-message relative p-4 rounded-lg bg-gradient-to-br from-orange-50 via-yellow-50 to-amber-50 dark:from-orange-900/30 dark:to-yellow-900/20 border-l-4 border-orange-400 shadow-sm">
                                    <div class="flex items-center gap-1 mb-2 text-orange-700 dark:text-orange-300 text-xs font-semibold uppercase tracking-wider">
                                        <span>📢</span>
                                        <span>Рекомендация</span>
                                    </div>
                                    <div class="text-sm text-gray-800 dark:text-gray-100 leading-relaxed">{!! $msg->content !!}</div>
                                    <div class="mt-2 text-xs text-gray-400 dark:text-gray-500 italic">Информационное сообщение</div>
                                </div>
                            @elseif($msg->role === 'assistant')
                                <div class="markdown-body text-sm raw-content hidden" data-raw="{{ e($msg->content) }}"></div>
                                <div class="text-sm raw-fallback whitespace-pre-wrap">{{ $msg->content }}</div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs opacity-75">{{ $msg->created_at->diffForHumans() }}</span>
                                    <button type="button" class="copy-btn text-xs opacity-70 hover:opacity-100" data-raw="{{ e($msg->content) }}" title="Копировать">📋</button>
                                    <button type="button" class="speak-btn text-xs opacity-70 hover:opacity-100" data-raw="{{ e($msg->content) }}" title="Озвучить ответ">🔊</button>
                                </div>
                            @else
                                @if($msg->file_name)
                                    <div class="mb-2 flex items-center gap-2 bg-blue-600 bg-opacity-30 rounded px-2 py-1.5 text-xs">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <a href="/storage/{{ $msg->file_path }}" target="_blank" class="underline hover:no-underline truncate">{{ $msg->file_name }}</a>
                                    </div>
                                @endif
                                <p class="text-sm whitespace-pre-wrap">{{ $msg->content }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs opacity-75">{{ $msg->created_at->diffForHumans() }}</span>
                                    <button type="button" class="copy-btn text-xs opacity-70 hover:opacity-100 text-white" data-raw="{{ e($msg->content) }}" title="Копировать">📋</button>
                                </div>
                            @endif
                @endforeach
            @else
                <div class="flex justify-start">
                    <div class="max-w-[85%] rounded-lg px-4 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                        <p class="text-sm">Здравствуйте! Я ваш AI-юрист. Задайте вопрос текстом или голосом 🎤</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 p-3 sm:p-4 bg-gray-50 dark:bg-gray-900 flex-shrink-0">
            <div id="file-preview" class="hidden mb-2 flex items-center gap-2 bg-blue-50 dark:bg-gray-700 rounded-lg px-3 py-2 text-sm">
                <svg class="w-5 h-5 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span id="file-name" class="truncate flex-1 text-gray-800 dark:text-gray-200"></span>
                <button type="button" id="file-remove" class="text-red-500 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('chat.stream') }}" class="flex gap-2" id="chat-form" enctype="multipart/form-data">
                @csrf
                @if($chatId)
                    <input type="hidden" name="chat_id" value="{{ $chatId }}">
                @endif
                <textarea 
                    name="message" 
                    id="message-input"
                    class="flex-1 min-w-0 border border-gray-300 dark:border-gray-600 rounded-lg px-3 sm:px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary dark:bg-gray-800 dark:text-gray-100 resize-none text-base"
                    placeholder="Задайте вопрос..."
                    rows="2"
                    @if(!$canSend) disabled @endif
                ></textarea>
                <input type="file" id="file-input" accept=".pdf,.docx,.txt,.doc" class="hidden" />
                <button type="button" id="attach-btn" 
                        class="flex items-center justify-center w-11 h-11 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex-shrink-0"
                        title="Прикрепить файл (PDF, DOCX, TXT)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                </button>
                <button type="button" id="mic-btn" 
                        class="hidden items-center justify-center w-11 h-11 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex-shrink-0"
                        title="Голосовой ввод">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5m-3.75-15a2.25 2.25 0 012.25 2.25v4.5a2.25 2.25 0 01-4.5 0v-4.5A2.25 2.25 0 0112 3z"/>
                    </svg>
                </button>
                <button 
                    type="submit" 
                    class="bg-primary hover:bg-blue-700 text-white px-4 sm:px-6 py-2.5 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                    @if(!$canSend) disabled @endif
                >
                    <span class="hidden sm:inline">Отправить</span>
                    <svg class="sm:hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
            @if(!$canSend)
                <p class="text-sm text-red-600 mt-2">
                    @if($isGuest)
                        Лимит исчерпан. <a href="{{ route('register') }}" class="underline">Зарегистрируйтесь</a>.
                    @else
                        Лимит исчерпан. <a href="{{ route('pricing') }}" class="underline">Выберите тариф</a>.
                    @endif
                </p>
            @endif
            
            @if($messages->isEmpty())
            <div class="quick-scroll flex sm:grid sm:grid-cols-2 gap-2 mt-3 overflow-x-auto sm:overflow-visible -mx-1 px-1">
                @foreach($quickPrompts->take(10) as $p)
                    <div class="quick-prompt-wrapper flex items-center gap-1 flex-shrink-0">
                        <button type="button" class="quick-prompt whitespace-nowrap sm:whitespace-normal text-left px-3 py-2 bg-blue-50 dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-600 transition text-sm flex-1" data-prompt-key="{{ $p->key }}">{{ $p->icon }} {{ $p->title }}</button>
                        <button type="button" class="share-prompt-btn flex-shrink-0 px-2 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition text-xs" data-prompt-key="{{ $p->key }}" title="Скопировать ссылку">📋</button>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 text-center">
                <a href="{{ route('prompts.index') }}" class="inline-flex items-center gap-1 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline transition">
                    📚 Весь каталог консультаций ({{ $quickPrompts->count() }} тем) →
                </a>
            </div>
            @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/marked@11.1.1/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === АВТООТПРАВКА ПО ССЫЛКЕ ?prompt=KEY ===
    const urlParams = new URLSearchParams(window.location.search);
    const autoPrompt = urlParams.get('prompt');
    if (autoPrompt) {
        const promptData = @json($quickPrompts->keyBy('key'));
        const promptText = promptData[autoPrompt]?.text;
        
        if (promptText) {
            // Небольшая задержка, чтобы всё загрузилось
            setTimeout(() => {
                const btn = document.querySelector(`.quick-prompt[data-prompt-key="${autoPrompt}"]`);
                if (btn) {
                    btn.click(); // Активируем chip с темой
                    
                    const inputEl = document.getElementById('message');
                    const formEl = document.getElementById('chat-form');
                    
                    if (inputEl && formEl) {
                        inputEl.value = promptText;
                        // Ещё задержка перед автоотправкой
                        setTimeout(() => {
                            if (inputEl.value.trim() && !inputEl.disabled) {
                                formEl.dispatchEvent(new Event('submit'));
                            }
                        }, 300);
                    }
                }
            }, 500);
        }
    }
    // === Markdown ===
    if (typeof marked !== 'undefined') {
        marked.setOptions({ breaks: true, gfm: true });
    }
    function renderMarkdown(text) {
        if (typeof marked === 'undefined') return text;
        try {
            const html = marked.parse(text);
            return typeof DOMPurify !== 'undefined' ? DOMPurify.sanitize(html) : html;
        } catch (e) { return text; }
    }
    document.querySelectorAll('.raw-content').forEach(el => {
        const raw = el.getAttribute('data-raw');
        if (raw) {
            el.innerHTML = renderMarkdown(raw);
            el.classList.remove('hidden');
            const fallback = el.nextElementSibling;
            if (fallback && fallback.classList.contains('raw-fallback')) fallback.remove();
        }
    });

    // === Mobile sidebar ===
    const sidebar = document.getElementById('chat-sidebar');
    const toggle = document.getElementById('mobile-menu-toggle');
    const closeBtn = document.getElementById('mobile-menu-close');
    const overlay = document.getElementById('mobile-overlay');

    // === Подгонка макета под реальную высоту шапки ===
    const headerEl = document.querySelector('header');
    function fixLayout() {
        if (!headerEl) return;
        const h = headerEl.offsetHeight;
        const page = document.getElementById('chat-page');
        const sb = document.getElementById('chat-sidebar');
        const ov = document.getElementById('mobile-overlay');
        if (page) page.style.height = 'calc(100dvh - ' + (h + 16) + 'px)';
        if (window.innerWidth < 768) {
            if (sb) { sb.style.top = h + 'px'; sb.style.height = 'calc(100dvh - ' + h + 'px)'; }
            if (ov) ov.style.top = h + 'px';
        } else {
            if (sb) { sb.style.top = ''; sb.style.height = ''; }
        }
    }
    fixLayout();
    window.addEventListener('resize', fixLayout);
    function openSidebar() { sidebar.classList.add('sidebar-open'); overlay.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
    function closeSidebar() { sidebar.classList.remove('sidebar-open'); overlay.classList.add('hidden'); document.body.style.overflow = ''; }
    toggle?.addEventListener('click', openSidebar);
    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);
    document.querySelectorAll('.chat-link').forEach(link => {
        link.addEventListener('click', function() { if (window.innerWidth < 768) closeSidebar(); });
    });

    // === Chat elements ===
    const form = document.getElementById('chat-form');
    const input = document.getElementById('message-input');
    const messagesContainer = document.getElementById('messages-container');
    
    // Прокрутка к последним сообщениям — усиленный режим:
    // повторяем ~4 секунды, используем scrollTop + scrollIntoView
    const forceScrollDown = () => {
        if (!messagesContainer) return;
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        const last = messagesContainer.lastElementChild;
        if (last) last.scrollIntoView({ block: 'end' });
    };
    let scrollAttempts = 0;
    const scrollLoop = () => {
        forceScrollDown();
        if (++scrollAttempts < 15) setTimeout(scrollLoop, 250);
    };
    scrollLoop();
    window.addEventListener('load', forceScrollDown);

    // === Голосовой ввод (Speech-to-Text) ===
    const micBtn = document.getElementById('mic-btn');
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    let recognition = null, listening = false, baseValue = '';

    if (SR && micBtn) {
        micBtn.classList.remove('hidden');
        micBtn.classList.add('flex');
        recognition = new SR();
        recognition.lang = 'ru-RU';
        recognition.continuous = true;
        recognition.interimResults = true;

        recognition.onresult = (event) => {
            let interim = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                const t = event.results[i][0].transcript;
                if (event.results[i].isFinal) baseValue += t + ' ';
                else interim += t;
            }
            input.value = baseValue + interim;
            input.dispatchEvent(new Event('input'));
        };
        recognition.onend = () => { if (listening) { try { recognition.start(); } catch(e){} } };
        recognition.onerror = (e) => {
            if (e.error === 'not-allowed' || e.error === 'service-not-allowed') {
                stopListening();
                alert('Нет доступа к микрофону. Разрешите доступ в настройках браузера.');
            }
        };
        micBtn.addEventListener('click', () => listening ? stopListening() : startListening());
    }

    function startListening() {
        baseValue = input.value ? input.value.trim() + ' ' : '';
        listening = true;
        try { recognition.start(); } catch(e){}
        micBtn.classList.add('mic-active');
    }
    function stopListening() {
        listening = false;
        try { recognition.stop(); } catch(e){}
        micBtn.classList.remove('mic-active');
    }

    // === Озвучка ответов (Text-to-Speech) ===
    const voiceToggle = document.getElementById('voice-toggle');
    let voiceMode = localStorage.getItem('nj_voice_mode') === '1';

    function speakText(text) {
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();
        const clean = text
            .replace(/```[\s\S]*?```/g, ' ')
            .replace(/\[([^\]]*)\]\([^)]*\)/g, '$1')
            .replace(/[*_#>`]/g, '')
            .replace(/\n+/g, '. ')
            .replace(/\s+/g, ' ')
            .trim();
        if (!clean) return;
        const u = new SpeechSynthesisUtterance(clean);
        u.lang = 'ru-RU';
        u.rate = 1;
        window.speechSynthesis.speak(u);
    }
    function stopSpeaking() { if ('speechSynthesis' in window) window.speechSynthesis.cancel(); }

    function updateVoiceToggle() {
        if (voiceMode) {
            voiceToggle.classList.add('bg-primary', 'text-white', 'border-primary');
        } else {
            voiceToggle.classList.remove('bg-primary', 'text-white', 'border-primary');
        }
    }
    voiceToggle?.addEventListener('click', () => {
        voiceMode = !voiceMode;
        localStorage.setItem('nj_voice_mode', voiceMode ? '1' : '0');
        updateVoiceToggle();
        if (!voiceMode) stopSpeaking();
        else speakText('Голосовой режим включён');
    });
    updateVoiceToggle();

    // Кнопка 🔊 на сообщениях
    messagesContainer?.addEventListener('click', (e) => {
        const btn = e.target.closest('.speak-btn');
        if (btn) speakText(btn.getAttribute('data-raw'));
    });
    // === Копирование сообщений ===
    function showToast(message) {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#16a34a;color:#fff;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9999;opacity:0;transition:opacity 0.3s;';
        document.body.appendChild(toast);
        setTimeout(() => toast.style.opacity = '1', 10);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }

    messagesContainer?.addEventListener('click', (e) => {
        const btn = e.target.closest('.copy-btn');
        if (!btn) return;
        
        const text = btn.getAttribute('data-raw');
        if (!text) return;
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('✓ Скопировано');
            }).catch(() => {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    });

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.cssText = 'position:fixed;top:-9999px;left:-9999px;';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showToast('✓ Скопировано');
        } catch (err) {
            showToast('✗ Ошибка копирования');
        }
        document.body.removeChild(textarea);
    }



    // === Поиск по чату ===
    const searchToggle = document.getElementById('search-toggle');
    const searchPanel = document.getElementById('search-panel');
    const searchInput = document.getElementById('search-input');
    const searchCount = document.getElementById('search-count');
    const searchPrev = document.getElementById('search-prev');
    const searchNext = document.getElementById('search-next');
    const searchClose = document.getElementById('search-close');
    let searchMarks = [], searchCurrent = -1, searchTimer = null;

    function clearSearchHighlights() {
        messagesContainer.querySelectorAll('mark.search-mark').forEach(m => {
            const parent = m.parentNode;
            parent.replaceChild(document.createTextNode(m.textContent), m);
            parent.normalize();
        });
        searchMarks = []; searchCurrent = -1;
    }

    function runSearch() {
        clearSearchHighlights();
        const q = searchInput.value.trim();
        if (q.length < 2) { searchCount.textContent = '0/0'; return; }
        const walker = document.createTreeWalker(messagesContainer, NodeFilter.SHOW_TEXT, {
            acceptNode: function(node) {
                if (!node.nodeValue || !node.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
                if (node.parentElement && node.parentElement.closest('script,style,mark.search-mark')) return NodeFilter.FILTER_REJECT;
                return node.nodeValue.toLowerCase().includes(q.toLowerCase()) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            }
        });
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        nodes.forEach(function(node) {
            const text = node.nodeValue;
            const lower = text.toLowerCase();
            const ql = q.toLowerCase();
            const frag = document.createDocumentFragment();
            let idx = 0, pos;
            while ((pos = lower.indexOf(ql, idx)) !== -1) {
                if (pos > idx) frag.appendChild(document.createTextNode(text.slice(idx, pos)));
                const mark = document.createElement('mark');
                mark.className = 'search-mark';
                mark.textContent = text.slice(pos, pos + q.length);
                frag.appendChild(mark);
                idx = pos + q.length;
            }
            if (idx < text.length) frag.appendChild(document.createTextNode(text.slice(idx)));
            node.parentNode.replaceChild(frag, node);
        });
        searchMarks = Array.from(messagesContainer.querySelectorAll('mark.search-mark'));
        searchCurrent = searchMarks.length ? 0 : -1;
        updateSearchCurrent(false);
    }

    function updateSearchCurrent(scroll = true) {
        searchMarks.forEach((m, i) => m.classList.toggle('search-current', i === searchCurrent));
        searchCount.textContent = searchMarks.length ? (searchCurrent + 1) + '/' + searchMarks.length : '0/0';
        if (scroll && searchCurrent >= 0) searchMarks[searchCurrent].scrollIntoView({ block: 'center', behavior: 'smooth' });
    }

    function goNext() { if (!searchMarks.length) return; searchCurrent = (searchCurrent + 1) % searchMarks.length; updateSearchCurrent(); }
    function goPrev() { if (!searchMarks.length) return; searchCurrent = (searchCurrent - 1 + searchMarks.length) % searchMarks.length; updateSearchCurrent(); }

    searchToggle?.addEventListener('click', () => {
        searchPanel.classList.toggle('hidden');
        if (!searchPanel.classList.contains('hidden')) searchInput.focus();
        else clearSearchHighlights();
    });
    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(runSearch, 300);
    });
    searchInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); e.shiftKey ? goPrev() : goNext(); }
    });
    searchNext?.addEventListener('click', goNext);
    searchPrev?.addEventListener('click', goPrev);
    searchClose?.addEventListener('click', () => {
        searchPanel.classList.add('hidden');
        clearSearchHighlights();
        searchInput.value = '';
        searchCount.textContent = '0/0';
    });


    // === Поиск по всем чатам ===
    const globalSearch = document.getElementById('global-chat-search');
    const globalResults = document.getElementById('global-search-results');
    let globalTimer = null;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    globalSearch?.addEventListener('input', function() {
        clearTimeout(globalTimer);
        const q = this.value.trim();
        if (q.length < 2) {
            globalResults.classList.add('hidden');
            globalResults.innerHTML = '';
            return;
        }
        globalTimer = setTimeout(async () => {
            try {
                const res = await fetch('{{ route("chat.search") }}?q=' + encodeURIComponent(q));
                const data = await res.json();
                if (!data.results.length) {
                    globalResults.innerHTML = '<div class="text-xs text-gray-500 dark:text-gray-400 text-center py-2">Ничего не найдено</div>';
                    globalResults.classList.remove('hidden');
                    return;
                }
                globalResults.innerHTML = data.results.map(r => `
                    <a href="{{ route('chat.show') }}?chat_id=${r.chat_id}" class="block px-3 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 border-l-2 border-primary">
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                            <span class="font-medium truncate">${r.role === 'user' ? '👤' : '🤖'} ${escapeHtml(r.chat_title)}</span>
                            <span class="flex-shrink-0 ml-2">${r.date}</span>
                        </div>
                        <div class="text-xs text-gray-700 dark:text-gray-300 leading-snug">${escapeHtml(r.excerpt)}</div>
                    </a>
                `).join('');
                globalResults.classList.remove('hidden');
            } catch (e) {}
        }, 300);
    });

    // === Chat logic ===
    input?.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 150) + 'px';
    });
    input?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (this.value.trim() && !this.disabled) form.dispatchEvent(new Event('submit'));
        }
    });
    let currentPromptKey = null;
    document.querySelectorAll('.quick-prompt').forEach(btn => {
        btn.addEventListener('click', function() {
            if (input && !input.disabled) {
                currentPromptKey = this.getAttribute('data-prompt-key');
                const topic = this.textContent.trim();
                const container = this.closest('.quick-scroll');
                if (container) container.classList.add('hidden');
                let chip = document.getElementById('prompt-topic-chip');
                if (!chip) {
                    chip = document.createElement('div');
                    chip.id = 'prompt-topic-chip';
                    chip.className = 'hidden items-center gap-2 text-xs text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg px-3 py-1.5 mt-2 w-fit';
                    chip.innerHTML = '<span id="prompt-topic-text"></span><button type="button" id="prompt-topic-clear" class="text-gray-400 hover:text-red-500 font-bold ml-1" title="Сбросить тему">✕</button>';
                    form.parentNode.insertBefore(chip, form);
                    chip.querySelector('#prompt-topic-clear').addEventListener('click', function() {
                        currentPromptKey = null;
                        chip.classList.add('hidden');
                        chip.classList.remove('flex');
                        if (container) container.classList.remove('hidden');
                    });
                }
                chip.querySelector('#prompt-topic-text').textContent = '📌 Тема: ' + topic;
                chip.classList.remove('hidden');
                chip.classList.add('flex');
                input.focus();
            }
        });
    });

    // === КОПИРОВАНИЕ ССЫЛКИ НА КВИК-ПРОМТ ===
    document.querySelectorAll('.share-prompt-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const key = this.getAttribute('data-prompt-key');
            const shareUrl = window.location.origin + window.location.pathname + '?prompt=' + encodeURIComponent(key);
            
            navigator.clipboard.writeText(shareUrl).then(() => {
                // Временная визуальная обратная связь
                const originalText = this.textContent;
                this.textContent = '✅';
                this.classList.add('bg-green-100', 'dark:bg-green-900');
                setTimeout(() => {
                    this.textContent = originalText;
                    this.classList.remove('bg-green-100', 'dark:bg-green-900');
                }, 1500);
            }).catch(err => {
                // Fallback для старых браузеров
                const textArea = document.createElement('textarea');
                textArea.value = shareUrl;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                const originalText = this.textContent;
                this.textContent = '✅';
                setTimeout(() => { this.textContent = originalText; }, 1500);
            });
        });
    });

    // === Файлы ===
    const attachBtn = document.getElementById('attach-btn');
    const fileInput = document.getElementById('file-input');
    const filePreview = document.getElementById('file-preview');
    const fileNameEl = document.getElementById('file-name');
    const fileRemove = document.getElementById('file-remove');
    let attachedFile = null;

    attachBtn?.addEventListener('click', () => fileInput?.click());
    fileInput?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const f = this.files[0];
            const ext = f.name.split('.').pop().toLowerCase();
            if (!['pdf', 'docx', 'txt', 'doc'].includes(ext)) {
                alert('Недопустимый формат. Разрешены: PDF, DOCX, TXT');
                this.value = '';
                return;
            }
            if (f.size > 10 * 1024 * 1024) {
                alert('Файл слишком большой. Максимум 10 МБ');
                this.value = '';
                return;
            }
            attachedFile = f;
            fileNameEl.textContent = f.name + ' (' + (f.size / 1024).toFixed(0) + ' КБ)';
            filePreview.classList.remove('hidden');
        }
    });
    fileRemove?.addEventListener('click', () => {
        attachedFile = null;
        fileInput.value = '';
        filePreview.classList.add('hidden');
    });

    form?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;
        if (listening) stopListening();
        stopSpeaking();
        
        const userMsg = document.createElement('div');
        userMsg.className = 'flex justify-end';
        userMsg.innerHTML = `<div class="max-w-[85%] rounded-lg px-4 py-2 bg-primary text-white"><p class="text-sm whitespace-pre-wrap">${message.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</p></div>`;
        messagesContainer.appendChild(userMsg);
        
        const loadingMsg = document.createElement('div');
        loadingMsg.className = 'flex justify-start';
        loadingMsg.innerHTML = `<div class="max-w-[85%] rounded-lg px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100"><div class="markdown-body text-sm assistant-message"><em class="opacity-60">Думаю...</em></div></div>`;
        messagesContainer.appendChild(loadingMsg);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Создаём FormData ПЕРЕД очисткой поля
        const formData = new FormData(form);
        formData.set('message', message); // Явно устанавливаем значение
        if (currentPromptKey) {
            formData.append('prompt_key', currentPromptKey);
        }
        if (attachedFile) {
            formData.append('attachment', attachedFile);
        }
        
        input.value = '';
        input.style.height = 'auto';
        input.disabled = true;
        
        try {
            const response = await fetch(form.action, { method: 'POST', body: formData });
            if (!response.ok) {
                let errorMsg = 'Ошибка ' + response.status;
                try {
                    const errData = await response.json();
                    if (errData.error) errorMsg = errData.error;
                } catch(e) {}
                throw new Error(errorMsg);
            }
            
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            const assistantText = loadingMsg.querySelector('.assistant-message');
            let fullText = '';
            let firstChunk = true;
            
            while (true) {
                const { done, value } = await reader.read();
                if (done) break;
                const chunk = decoder.decode(value);
                for (const line of chunk.split('\n')) {
                    if (line.startsWith('data: ') && line.slice(6) !== '[DONE]') {
                        try {
                            const json = JSON.parse(line.slice(6));
                            if (json.content) {
                                if (firstChunk) { assistantText.innerHTML = ''; firstChunk = false; }
                                fullText += json.content;
                                assistantText.innerHTML = renderMarkdown(fullText);
                                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                            }
                            if (json.ad) {
                                // Добавляем рекламное сообщение отдельным блоком
                                const adMsg = document.createElement('div');
                                adMsg.className = 'flex justify-start';
                                let adHtml = `<div class="max-w-[85%] sm:max-w-[80%] rounded-lg p-4 bg-gradient-to-br from-orange-50 via-yellow-50 to-amber-50 dark:from-orange-900/30 dark:to-yellow-900/20 border-l-4 border-orange-400 shadow-sm">`;
                                adHtml += `<div class="flex items-center gap-1 mb-2 text-orange-700 dark:text-orange-300 text-xs font-semibold uppercase tracking-wider"><span>📢</span><span>Рекомендация</span></div>`;
                                adHtml += `<div class="text-sm text-gray-800 dark:text-gray-100 leading-relaxed">` + json.content + `</div>`;
                                if (json.cta_text && json.cta_url) {
                                    adHtml += `<a href="` + json.cta_url + `" class="inline-block mt-3 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-sm font-medium">` + json.cta_text + `</a>`;
                                }
                                adHtml += `<div class="mt-2 text-xs text-gray-400 dark:text-gray-500 italic">Информационное сообщение</div>`;
                                adHtml += `</div>`;
                                adMsg.innerHTML = adHtml;
                                messagesContainer.appendChild(adMsg);
                                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                            }
                        } catch (e) {}
                    }
                }
            }
            if (voiceMode && fullText) {
                // Озвучиваем ответ, ждём окончания, потом перезагружаем
                const u = new SpeechSynthesisUtterance(fullText
                    .replace(/\`\`\`[\s\S]*?\`\`\`/g, ' ')
                    .replace(/\[([^\]]*)\]\([^)]*\)/g, '$1')
                    .replace(/[*_#>\`]/g, '')
                    .replace(/\n+/g, '. ')
                    .replace(/\s+/g, ' ')
                    .trim());
                u.lang = 'ru-RU';
                u.rate = 1;
                const resetAndReload = () => {
                    attachedFile = null;
                    fileInput.value = '';
                    filePreview.classList.add('hidden');
                    sessionStorage.setItem('scroll_to_bottom', '1');
                    setTimeout(() => window.location.reload(), 300);
                };
                u.onend = resetAndReload;
                u.onerror = resetAndReload;
                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(u);
            } else {
                attachedFile = null;
            fileInput.value = '';
            filePreview.classList.add('hidden');
            sessionStorage.setItem('scroll_to_bottom', '1');
            setTimeout(() => window.location.reload(), 800);
            }
        } catch (error) {
            loadingMsg.innerHTML = `<div class="max-w-[85%] rounded-lg px-4 py-2 bg-red-100 text-red-800"><p class="text-sm">Ошибка отправки. Попробуйте ещё раз.</p></div>`;
            input.disabled = false;
        }
    });
});
</script>
@endpush
@endsection
