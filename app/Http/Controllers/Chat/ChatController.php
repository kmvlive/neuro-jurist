<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private const FREE_LIMIT = 20;

    /**
     * Показать страницу чата (доступно без авторизации)
     */
    public function show(Request $request)
    {
        $guestId = $this->getGuestId($request);
        $isGuest = !Auth::check();
        $messagesUsed = 0;
        $canSend = true;
        $remainingMessages = $this->FREE_LIMIT;

        if ($isGuest) {
            // Гость: проверяем лимит через session
            $messagesUsed = session('guest_messages_used', 0);
            $canSend = $messagesUsed < $this->FREE_LIMIT;
            $remainingMessages = max(0, $this->FREE_LIMIT - $messagesUsed);
        } else {
            // Авторизованный пользователь
            $user = Auth::user();
            $messagesUsed = $user->free_messages_used;
            $canSend = $user->canSendMessages();
            $remainingMessages = $user->getRemainingFreeMessages();
        }

        // Получаем чаты для отображения в сайдбаре
        $chats = $this->getChatsForUser($guestId);

        return view('chat.show', [
            'chats' => $chats,
            'currentChat' => null,
            'messages' => [],
            'isGuest' => $isGuest,
            'messagesUsed' => $messagesUsed,
            'canSend' => $canSend,
            'remainingMessages' => $remainingMessages,
            'freeLimit' => $this->FREE_LIMIT,
        ]);
    }

    /**
     * Отправка сообщения в чат
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'chat_id' => ['nullable', 'exists:chats,id'],
        ]);

        $guestId = $this->getGuestId($request);
        $isGuest = !Auth::check();

        // Проверка лимита
        if ($isGuest) {
            $messagesUsed = session('guest_messages_used', 0);
            if ($messagesUsed >= $this->FREE_LIMIT) {
                return response()->json([
                    'success' => false,
                    'limit_exceeded' => true,
                    'message' => 'Бесплатный лимит исчерпан. Зарегистрируйтесь, чтобы сохранить историю консультаций.',
                ], 403);
            }
        } else {
            $user = Auth::user();
            if (!$user->canSendMessages()) {
                return response()->json([
                    'success' => false,
                    'limit_exceeded' => true,
                    'message' => 'Бесплатный лимит исчерпан. Выберите тариф, чтобы продолжить.',
                ], 403);
            }
        }

        // Создаём или получаем чат
        if ($request->chat_id) {
            $chat = Chat::findOrFail($request->chat_id);
            // Проверка прав доступа
            if ($isGuest) {
                if ($chat->guest_id !== $guestId || $chat->user_id !== null) {
                    abort(403);
                }
            } else {
                if ($chat->user_id !== Auth::id()) {
                    abort(403);
                }
            }
        } else {
            $chat = Chat::create([
                'user_id' => $isGuest ? null : Auth::id(),
                'guest_id' => $isGuest ? $guestId : null,
                'title' => Str::limit($request->content, 50),
            ]);
        }

        // Сохраняем сообщение пользователя
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $request->content,
        ]);

        // Увеличиваем счётчик
        if ($isGuest) {
            session(['guest_messages_used' => $messagesUsed + 1]);
        } else {
            Auth::user()->incrementFreeMessagesUsed();
        }

        // Заглушка для AI-ответа
        $aiResponse = "Интеграция AI будет подключена в следующем обновлении";
        
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => $aiResponse,
        ]);

        return response()->json([
            'success' => true,
            'chat_id' => $chat->id,
            'user_message' => $request->content,
            'ai_response' => $aiResponse,
            'remaining_messages' => $isGuest 
                ? max(0, $this->FREE_LIMIT - session('guest_messages_used', 0))
                : Auth::user()->getRemainingFreeMessages(),
        ]);
    }

    /**
     * Получить историю чатов
     */
    public function getChats(Request $request)
    {
        $guestId = $this->getGuestId($request);
        $chats = $this->getChatsForUser($guestId);

        return response()->json([
            'chats' => $chats->map(fn($chat) => [
                'id' => $chat->id,
                'title' => $chat->title ?? 'Новый чат',
                'created_at' => $chat->created_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * Получить сообщения конкретного чата
     */
    public function getChatMessages($chatId)
    {
        $chat = Chat::findOrFail($chatId);
        $guestId = $this->getGuestId(request());
        $isGuest = !Auth::check();

        // Проверка прав доступа
        if ($isGuest) {
            if ($chat->guest_id !== $guestId || $chat->user_id !== null) {
                abort(403);
            }
        } else {
            if ($chat->user_id !== Auth::id()) {
                abort(403);
            }
        }

        $messages = $chat->messages()->orderBy('created_at')->get();

        return response()->json([
            'chat' => [
                'id' => $chat->id,
                'title' => $chat->title,
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Создать новый чат
     */
    public function create()
    {
        $guestId = $this->getGuestId(request());
        $isGuest = !Auth::check();

        // Проверка лимита для создания нового чата
        if ($isGuest) {
            $messagesUsed = session('guest_messages_used', 0);
            if ($messagesUsed >= $this->FREE_LIMIT) {
                return redirect()->route('pricing')
                    ->with('error', 'Бесплатный лимит исчерпан. Зарегистрируйтесь, чтобы продолжить.');
            }
        }

        $chat = Chat::create([
            'user_id' => $isGuest ? null : Auth::id(),
            'guest_id' => $isGuest ? $guestId : null,
            'title' => 'Новый чат',
        ]);

        return redirect()->route('chat.show', $chat->id);
    }

    /**
     * Удалить чат
     */
    public function destroy($chatId)
    {
        $chat = Chat::findOrFail($chatId);
        $guestId = $this->getGuestId(request());
        $isGuest = !Auth::check();

        // Проверка прав доступа
        if ($isGuest) {
            if ($chat->guest_id !== $guestId || $chat->user_id !== null) {
                abort(403);
            }
        } else {
            if ($chat->user_id !== Auth::id()) {
                abort(403);
            }
        }

        $chat->delete();

        return redirect()->route('chat.show')
            ->with('success', 'Чат удалён.');
    }

    /**
     * Получить или создать guest_id
     */
    private function getGuestId(Request $request): ?string
    {
        if (Auth::check()) {
            return null;
        }

        $guestId = $request->cookie('nj_guest_id');
        
        if (!$guestId) {
            $guestId = (string) Str::uuid();
            cookie()->queue('nj_guest_id', $guestId, 43200); // 30 дней
        }

        return $guestId;
    }

    /**
     * Получить чаты для текущего пользователя или гостя
     */
    private function getChatsForUser(?string $guestId)
    {
        if (Auth::check()) {
            return Auth::user()->chats()->latest()->take(20)->get();
        }

        return Chat::forGuest($guestId)->latest()->take(20)->get();
    }

    /**
     * Привязать чаты гостя к пользователю после регистрации
     */
    public static function attachGuestChatsToUser(User $user, string $guestId): void
    {
        Chat::where('guest_id', $guestId)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);
    }
}
