<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Services\AI\TimewebAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private const FREE_LIMIT = 20;

    public function show(Request $request)
    {
        $guestId = $this->getGuestId($request);
        $isGuest = !Auth::check();
        $messagesUsed = 0;
        $canSend = true;
        $remainingMessages = self::FREE_LIMIT;
        $currentChat = null;
        $messages = collect([]);

        // Если указан chat_id — открываем его
        if ($request->filled('chat_id')) {
            $currentChat = Chat::find($request->chat_id);
            if ($currentChat) {
                if ($isGuest) {
                    if ($currentChat->guest_id !== $guestId || $currentChat->user_id !== null) {
                        $currentChat = null;
                    }
                } else {
                    if ($currentChat->user_id !== Auth::id()) {
                        $currentChat = null;
                    }
                }
            }
        }

        // Если чат не выбран — берём последний активный чат пользователя/гостя
        if (!$currentChat) {
            if ($isGuest) {
                $currentChat = Chat::where('guest_id', $guestId)
                    ->whereNull('user_id')
                    ->latest()
                    ->first();
            } else {
                $currentChat = Auth::user()->chats()->latest()->first();
            }
        }

        if ($currentChat) {
            $messages = $currentChat->messages()->orderBy('created_at')->get();
        }

        if ($isGuest) {
            $messagesUsed = session('guest_messages_used', 0);
            $canSend = $messagesUsed < self::FREE_LIMIT;
            $remainingMessages = max(0, self::FREE_LIMIT - $messagesUsed);
        } else {
            $user = Auth::user();
            $messagesUsed = $user->free_messages_used;
            $canSend = $user->canSendMessages();
            $remainingMessages = $user->getRemainingFreeMessages();
        }

        $chats = $this->getChatsForUser($guestId);

        return view('chat.show', [
            'chats' => $chats,
            'currentChat' => $currentChat,
            'chatId' => $currentChat ? $currentChat->id : null,
            'messages' => $messages,
            'isGuest' => $isGuest,
            'messagesUsed' => $messagesUsed,
            'canSend' => $canSend,
            'remainingMessages' => $remainingMessages,
            'freeLimit' => self::FREE_LIMIT,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $content = trim((string) $request->input('message', $request->input('content')));

        if ($content === '' || mb_strlen($content) > 5000) {
            return back()->with('error', 'Сообщение должно содержать от 1 до 5000 символов.');
        }

        $guestId = $this->getGuestId($request);
        $isGuest = !Auth::check();

        if ($isGuest) {
            $messagesUsed = session('guest_messages_used', 0);
            if ($messagesUsed >= self::FREE_LIMIT) {
                return back()->with('error', 'Бесплатный лимит исчерпан. Зарегистрируйтесь, чтобы сохранить историю консультаций.');
            }
        } else {
            $user = Auth::user();
            if (!$user->canSendMessages()) {
                return back()->with('error', 'Бесплатный лимит исчерпан. Выберите тариф, чтобы продолжить.');
            }
        }

        if ($request->filled('chat_id')) {
            $chat = Chat::findOrFail($request->chat_id);
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
                'title' => Str::limit($content, 50),
            ]);
        }

        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $content,
        ]);

        if ($isGuest) {
            $messagesUsed = session('guest_messages_used', 0);
            session(['guest_messages_used' => $messagesUsed + 1]);
        } else {
            Auth::user()->incrementFreeMessagesUsed();
        }

        $history = $chat->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        try {
            $aiService = new TimewebAIService();
            $aiResponse = $aiService->chat($content, $history);
        } catch (\Throwable $e) {
            Log::error('Timeweb AI error: ' . $e->getMessage());
            $aiResponse = 'Извините, сервис временно перегружен. Пожалуйста, попробуйте ещё раз через минуту.';
        }

        Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => $aiResponse,
        ]);

        return redirect()->route('chat.show', ['chat_id' => $chat->id]);
    }

    public function stream(Request $request)
    {
        $content = trim((string) $request->input('message', $request->input('content')));

        if ($content === '' || mb_strlen($content) > 5000) {
            return response()->json(['error' => 'Некорректное сообщение'], 422);
        }

        $guestId = $this->getGuestId($request);
        $isGuest = !Auth::check();

        if ($isGuest) {
            $messagesUsed = session('guest_messages_used', 0);
            if ($messagesUsed >= self::FREE_LIMIT) {
                return response()->json(['error' => 'Лимит исчерпан'], 403);
            }
        } else {
            $user = Auth::user();
            if (!$user->canSendMessages()) {
                return response()->json(['error' => 'Лимит исчерпан'], 403);
            }
        }

        if ($request->filled('chat_id')) {
            $chat = Chat::findOrFail($request->chat_id);
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
                'title' => Str::limit($content, 50),
            ]);
        }

        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $content,
        ]);

        if ($isGuest) {
            $messagesUsed = session('guest_messages_used', 0);
            session(['guest_messages_used' => $messagesUsed + 1]);
        } else {
            Auth::user()->incrementFreeMessagesUsed();
        }

        $history = $chat->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $aiService = new TimewebAIService();
        $fullResponse = '';

        return response()->stream(function () use ($aiService, $content, $history, $chat, &$fullResponse) {
            try {
                foreach ($aiService->chatStream($content, $history) as $chunk) {
                    $fullResponse .= $chunk;
                    echo "data: " . json_encode(['content' => $chunk]) . "\n\n";
                    ob_flush();
                    flush();
                }
                echo "data: " . json_encode(['done' => true, 'chat_id' => $chat->id]) . "\n\n";
                ob_flush();
                flush();

                Message::create([
                    'chat_id' => $chat->id,
                    'role' => 'assistant',
                    'content' => $fullResponse,
                ]);
            } catch (\Throwable $e) {
                Log::error('Stream error: ' . $e->getMessage());
                echo "data: " . json_encode(['error' => 'Ошибка сервиса']) . "\n\n";
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

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

    public function getChatMessages($chatId)
    {
        $chat = Chat::findOrFail($chatId);
        $guestId = $this->getGuestId(request());
        $isGuest = !Auth::check();

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

    public function create()
    {
        $guestId = $this->getGuestId(request());
        $isGuest = !Auth::check();

        if ($isGuest) {
            $messagesUsed = session('guest_messages_used', 0);
            if ($messagesUsed >= self::FREE_LIMIT) {
                return redirect()->route('pricing')
                    ->with('error', 'Бесплатный лимит исчерпан. Зарегистрируйтесь, чтобы продолжить.');
            }
        }

        $chat = Chat::create([
            'user_id' => $isGuest ? null : Auth::id(),
            'guest_id' => $isGuest ? $guestId : null,
            'title' => 'Новый чат',
        ]);

        return redirect()->route('chat.show', ['chat_id' => $chat->id]);
    }

    public function destroy($chatId)
    {
        $chat = Chat::findOrFail($chatId);
        $guestId = $this->getGuestId(request());
        $isGuest = !Auth::check();

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

    private function getGuestId(Request $request): ?string
    {
        if (Auth::check()) {
            return null;
        }

        $guestId = $request->cookie('nj_guest_id');

        if (!$guestId) {
            $guestId = (string) Str::uuid();
            cookie()->queue('nj_guest_id', $guestId, 43200);
        }

        return $guestId;
    }

    private function getChatsForUser(?string $guestId)
    {
        if (Auth::check()) {
            return Auth::user()->chats()->latest()->take(20)->get();
        }

        return Chat::forGuest($guestId)->latest()->take(20)->get();
    }

    public static function attachGuestChatsToUser(User $user, string $guestId): void
    {
        Chat::where('guest_id', $guestId)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);
    }
}
