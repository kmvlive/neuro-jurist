@extends('layouts.app')

@section('title', 'Нейро-юрист — AI-ассистент')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-[calc(100vh-200px)] flex flex-col">
    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('chat.show') }}" class="text-primary dark:text-blue-400 hover:underline">← Новый чат</a>
        @if($chatId)
        <form method="POST" action="{{ route('chat.destroy', $chatId) }}"
              onsubmit="return confirm('Удалить этот чат?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm">Удалить чат</button>
        </form>
        @endif
    </div>

    @if(session('error'))
        <div class="bg-red-100 dark:bg-red-900/40 border border-red-400 text-red-800 dark:text-red-200 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex-grow bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden flex flex-col transition-colors">
        <div class="flex-grow p-4 overflow-y-auto space-y-4" id="messages-container">
            @if($currentChat && $messages->count() > 0)
                @foreach($messages as $msg)
                    <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-lg px-4 py-2 {{ $msg->role === 'user' ? 'bg-primary text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100' }}">
                            <p class="text-sm whitespace-pre-wrap">{{ $msg->content }}</p>
                            <span class="text-xs opacity-75 mt-1 block">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="flex justify-start">
                    <div class="max-w-[85%] rounded-lg px-4 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                        <p class="text-sm mb-3">Здравствуйте! Я ваш AI-юрист. Задайте любой вопрос или выберите одну из тем:</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2" id="quick-prompts">
                    <button type="button" class="quick-prompt text-left px-4 py-3 bg-blue-50 dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-600 transition text-sm text-gray-800 dark:text-gray-100">
                        📄 Составь претензию в магазин за некачественный товар
                    </button>
                    <button type="button" class="quick-prompt text-left px-4 py-3 bg-blue-50 dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-600 transition text-sm text-gray-800 dark:text-gray-100">
                        ⚖️ Какие права у работника при сокращении?
                    </button>
                    <button type="button" class="quick-prompt text-left px-4 py-3 bg-blue-50 dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-600 transition text-sm text-gray-800 dark:text-gray-100">
                        🏠 Как проверить договор аренды квартиры?
                    </button>
                    <button type="button" class="quick-prompt text-left px-4 py-3 bg-blue-50 dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-600 transition text-sm text-gray-800 dark:text-gray-100">
                        💰 Что делать, если задерживают зарплату?
                    </button>
                    <button type="button" class="quick-prompt text-left px-4 py-3 bg-blue-50 dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-600 transition text-sm text-gray-800 dark:text-gray-100">
                        👨‍👩‍👧 Как взыскать алименты через суд?
                    </button>
                    <button type="button" class="quick-prompt text-left px-4 py-3 bg-blue-50 dark:bg-gray-700 border border-blue-200 dark:border-gray-600 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-600 transition text-sm text-gray-800 dark:text-gray-100">
                        🚗 Как обжаловать штраф ГИБДД?
                    </button>
                </div>
            @endif
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900 transition-colors">
            <form method="POST" action="{{ route('chat.stream') }}" class="flex space-x-4" id="chat-form">
                @csrf
                <input type="hidden" name="chat_id" value="{{ $chatId ?? '' }}">
                <textarea name="message" rows="2"
                    class="flex-grow px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary resize-none"
                    placeholder="Введите ваш вопрос..."
                    required></textarea>
                <button type="submit" id="send-btn"
                    class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700 self-end">
                    Отправить
                </button>
            </form>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Бесплатно: {{ $remainingMessages }} из {{ $freeLimit }} сообщений.
                @if($isGuest) · <a href="{{ route('register') }}" class="text-primary dark:text-blue-400 hover:underline">Зарегистрируйтесь</a>, чтобы сохранить историю.
                @endif
            </div>
        </div>
    </div>
</div>

@if($isGuest && !$canSend)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-8 max-w-md mx-4 text-gray-900 dark:text-gray-100">
        <h3 class="text-xl font-bold mb-4">Бесплатный лимит исчерпан</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Зарегистрируйтесь, чтобы сохранить историю консультаций и продолжить общение.</p>
        <div class="flex space-x-4">
            <a href="{{ route('register') }}" class="flex-1 bg-primary text-white text-center py-2 rounded-lg hover:bg-blue-700">Зарегистрироваться</a>
            <a href="{{ route('login') }}" class="flex-1 border border-primary text-primary dark:border-blue-400 dark:text-blue-400 text-center py-2 rounded-lg hover:bg-blue-50 dark:hover:bg-gray-700">У меня уже есть аккаунт</a>
        </div>
    </div>
</div>
@endif

@if(!$isGuest && !$canSend)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-8 max-w-md mx-4 text-center text-gray-900 dark:text-gray-100">
        <h3 class="text-xl font-bold mb-4">Лимит бесплатных сообщений исчерпан</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Выберите тариф, чтобы продолжить пользоваться сервисом без ограничений.</p>
        <a href="{{ route('pricing') }}" class="inline-block bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700">Перейти к тарифам</a>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("chat-form");
    const textarea = form.querySelector("textarea[name='message']");
    const sendBtn = document.getElementById("send-btn");
    const messagesContainer = document.getElementById("messages-container");

    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    if (textarea) {
        textarea.addEventListener("input", function() {
            this.style.height = "auto";
            this.style.height = (this.scrollHeight) + "px";
        });

        textarea.addEventListener("keydown", function(e) {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event("submit", { cancelable: true }));
            }
        });
    }

    document.querySelectorAll(".quick-prompt").forEach(btn => {
        btn.addEventListener("click", function() {
            const text = this.textContent.trim();
            textarea.value = text;
            textarea.dispatchEvent(new Event("input"));
            form.dispatchEvent(new Event("submit", { cancelable: true }));
        });
    });

    async function sendMessage(messageText) {
        sendBtn.disabled = true;
        sendBtn.textContent = "Отправка...";
        textarea.disabled = true;

        const quickPrompts = document.getElementById("quick-prompts");
        if (quickPrompts) quickPrompts.style.display = "none";

        const userMsgHtml = `
            <div class="flex justify-end">
                <div class="max-w-[80%] rounded-lg px-4 py-2 bg-primary text-white">
                    <p class="text-sm whitespace-pre-wrap">${messageText.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</p>
                    <span class="text-xs opacity-75 mt-1 block">Только что</span>
                </div>
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', userMsgHtml);

        const botMsgId = 'bot-msg-' + Date.now();
        const botMsgHtml = `
            <div class="flex justify-start" id="${botMsgId}">
                <div class="max-w-[80%] rounded-lg px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                    <p class="text-sm whitespace-pre-wrap" id="${botMsgId}-content"></p>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">Печатает...</span>
                </div>
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', botMsgHtml);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        textarea.value = '';
        textarea.style.height = "auto";

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "text/event-stream"
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || "Ошибка сети");
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            const botContentEl = document.getElementById(`${botMsgId}-content`);
            let fullText = '';

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                const chunk = decoder.decode(value, { stream: true });
                const lines = chunk.split('\n');

                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        const jsonStr = line.substring(6);
                        try {
                            const data = JSON.parse(jsonStr);
                            if (data.content) {
                                fullText += data.content;
                                botContentEl.textContent = fullText;
                                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                            }
                            if (data.done) {
                                const timeSpan = document.querySelector(`#${botMsgId} span`);
                                if (timeSpan) timeSpan.textContent = 'Только что';
                                if (data.chat_id) {
                                    const newUrl = window.location.pathname + '?chat_id=' + data.chat_id;
                                    window.history.replaceState({}, document.title, newUrl);
                                }
                            }
                            if (data.error) {
                                throw new Error(data.error);
                            }
                        } catch (parseError) {
                            console.error('Parse error:', parseError);
                        }
                    }
                }
            }
        } catch (error) {
            console.error("Ошибка:", error);
            const errorMsgHtml = `
                <div class="flex justify-start">
                    <div class="max-w-[80%] rounded-lg px-4 py-2 bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200">
                        <p class="text-sm">Ошибка: ${error.message}</p>
                    </div>
                </div>
            `;
            messagesContainer.insertAdjacentHTML('beforeend', errorMsgHtml);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        } finally {
            sendBtn.disabled = false;
            sendBtn.textContent = "Отправить";
            textarea.disabled = false;
            textarea.focus();
        }
    }

    if (form) {
        form.addEventListener("submit", async function(e) {
            e.preventDefault();
            const messageText = textarea.value.trim();
            if (!messageText) return;
            await sendMessage(messageText);
        });
    }
});
</script>
@endpush
