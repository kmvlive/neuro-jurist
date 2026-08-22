@extends('layouts.app')

@section('title', 'Чат — Нейро-юрист')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-[calc(100vh-200px)] flex flex-col">
    <!-- Инфо о лимитах -->
    <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
        <div class="flex justify-between items-center">
            <span class="text-sm text-gray-700">
                @if($isGuest)
                    Бесплатных сообщений осталось: <strong>{{ $remainingMessages }}</strong> из {{ $freeLimit }}
                @else
                    @if(auth()->user()->hasActiveSubscription())
                        <span class="text-green-600">✓ Активная подписка — безлимитные сообщения</span>
                    @else
                        Бесплатных сообщений осталось: <strong>{{ $remainingMessages }}</strong> из {{ $freeLimit }}
                    @endif
                @endif
            </span>
            @if(!$isGuest && !auth()->user()->hasActiveSubscription() && $remainingMessages == 0)
                <a href="{{ route('pricing') }}" class="text-primary hover:underline font-semibold">→ Выбрать тариф</a>
            @endif
        </div>
    </div>

    <!-- Область чата -->
    <div class="flex-grow bg-white rounded-lg shadow overflow-hidden flex flex-col">
        @if($canSend)
            <!-- Форма отправки сообщения -->
            <div class="border-b p-4 bg-gray-50">
                <form id="chat-form" class="flex space-x-4">
                    @csrf
                    <textarea name="message" id="message-input" rows="2" 
                        class="flex-grow px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary resize-none"
                        placeholder="Введите ваш вопрос..."
                        required></textarea>
                    <button type="submit" id="send-btn"
                        class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700 self-end">
                        Отправить
                    </button>
                </form>
            </div>
            
            <!-- Сообщения -->
            <div class="flex-grow p-4 overflow-y-auto space-y-4" id="messages-container">
                <!-- Приветственное сообщение -->
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white">
                        ⚖️
                    </div>
                    <div class="bg-gray-100 rounded-lg px-4 py-2 max-w-[80%]">
                        <p class="text-sm text-gray-900">Здравствуйте! Я ваш AI-юрист. Чем могу помочь?</p>
                        <span class="text-xs text-gray-500 mt-1 block">Только что</span>
                    </div>
                </div>
                
                <!-- Сюда будут добавляться сообщения -->
            </div>
        @else
            <!-- Экран блокировки при исчерпании лимита -->
            <div class="flex-grow flex items-center justify-center p-8">
                <div class="text-center max-w-md">
                    @if($isGuest)
                        <div class="text-6xl mb-4">🔒</div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Бесплатный лимит исчерпан</h2>
                        <p class="text-gray-600 mb-6">Зарегистрируйтесь, чтобы сохранить историю консультаций и продолжить общение</p>
                        <div class="space-y-3">
                            <a href="{{ route('register') }}" class="block w-full bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-semibold">
                                Зарегистрироваться
                            </a>
                            <a href="{{ route('login') }}" class="block w-full border-2 border-primary text-primary px-6 py-3 rounded-lg hover:bg-blue-50 font-semibold">
                                У меня уже есть аккаунт
                            </a>
                        </div>
                    @else
                        <div class="text-6xl mb-4">⏰</div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Лимит бесплатных сообщений исчерпан</h2>
                        <p class="text-gray-600 mb-6">Выберите подходящий тариф, чтобы продолжить пользоваться сервисом</p>
                        <a href="{{ route('pricing') }}" class="inline-block bg-primary text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-semibold">
                            Выбрать тариф
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const messagesContainer = document.getElementById('messages-container');
    const sendBtn = document.getElementById('send-btn');
    
    if (!chatForm || !messageInput || !sendBtn || !messagesContainer) {
        console.log('Chat form elements not found');
        return;
    }

@if($canSend)
    // Обработка нажатия Enter
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });
    
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = messageInput.value.trim();
        if (!message) return;
        
        // Блокируем кнопку на время отправки
        sendBtn.disabled = true;
        sendBtn.textContent = 'Отправка...';
        
        try {
            const response = await fetch('{{ route('chat.send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ content: message })
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                // Добавляем сообщение пользователя
                addMessage(message, 'user');
                
                // Добавляем ответ AI
                addMessage(data.ai_response, 'assistant');
                
                // Очищаем поле ввода
                messageInput.value = '';
                
                // Обновляем счётчик (если нужно)
                // Можно добавить обновление UI с оставшимися сообщениями
                
                // Проверяем, не исчерпан ли лимит
                if (data.remaining_messages === 0) {
                    setTimeout(() => location.reload(), 1000);
                }
            } else if (data.limit_exceeded) {
                alert(data.message);
                location.reload();
            } else {
                alert('Ошибка при отправке сообщения');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Произошла ошибка при отправке сообщения');
        } finally {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Отправить';
        }
    });
@endif

    function addMessage(content, role) {
        const isUser = role === 'user';
        const messageHtml = `
            <div class="flex items-start space-x-3 ${isUser ? 'flex-row-reverse space-x-reverse' : ''}">
                <div class="flex-shrink-0 w-8 h-8 ${isUser ? 'bg-gray-500' : 'bg-primary'} rounded-full flex items-center justify-center text-white">
                    ${isUser ? '👤' : '⚖️'}
                </div>
                <div class="${isUser ? 'bg-blue-100' : 'bg-gray-100'} rounded-lg px-4 py-2 max-w-[80%]">
                    <p class="text-sm text-gray-900">${escapeHtml(content)}</p>
                    <span class="text-xs text-gray-500 mt-1 block">Только что</span>
                </div>
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@endpush
@endsection
