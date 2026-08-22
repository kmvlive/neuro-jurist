@extends('layouts.app')

@section('title', 'Чат #' . $chatId . ' — Нейро-юрист')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 h-[calc(100vh-200px)] flex flex-col">
    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('chat.index') }}" class="text-primary hover:underline">← Назад к чатам</a>
        <form method="POST" action="{{ route('chat.destroy', $chatId) }}" 
              onsubmit="return confirm('Вы уверены, что хотите удалить этот чат?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-800">Удалить чат</button>
        </form>
    </div>

    <!-- Область сообщений -->
    <div class="flex-grow bg-white rounded-lg shadow overflow-hidden flex flex-col">
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
            
            <!-- Здесь будут сообщения -->
        </div>

        <!-- Форма отправки сообщения -->
        <div class="border-t p-4 bg-gray-50">
            <form method="POST" action="{{ route('chat.message', $chatId) }}" class="flex space-x-4">
                @csrf
                <textarea name="message" rows="2" 
                    class="flex-grow px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary resize-none"
                    placeholder="Введите ваш вопрос..."
                    required></textarea>
                <button type="submit" 
                    class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-blue-700 self-end">
                    Отправить
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Автопрокрутка к последнему сообщению
const messagesContainer = document.getElementById('messages-container');
messagesContainer.scrollTop = messagesContainer.scrollHeight;
</script>
@endpush
@endsection
