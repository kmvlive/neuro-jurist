<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Models\QuickPrompt;
use App\Models\Ad;
use App\Services\DocumentParser;
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
            $messagesUsed = Message::whereHas('chat', function ($q) use ($guestId) {
                $q->where('guest_id', $guestId)->whereNull('user_id');
            })->where('role', 'user')->count();
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
            'quickPrompts' => QuickPrompt::getForChat(),
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
            $messagesUsed = Message::whereHas('chat', function ($q) use ($guestId) {
                $q->where('guest_id', $guestId)->whereNull('user_id');
            })->where('role', 'user')->count();
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
                'prompt_key' => $request->input('prompt_key'),
            ]);
        }

        // === ОБРАБОТКА ЗАГРУЖЕННОГО ФАЙЛА ===
        $fileName = null;
        $filePath = null;
        $fileText = '';
        
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $ext = strtolower($file->getClientOriginalExtension());
            $allowed = DocumentParser::ALLOWED_EXT;
            
            if (!in_array($ext, $allowed)) {
                return back()->with('error', 'Недопустимый формат файла. Разрешены: ' . implode(', ', $allowed));
            }
            
            if ($file->getSize() > 10 * 1024 * 1024) {
                return back()->with('error', 'Файл слишком большой. Максимум 10 МБ.');
            }
            
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('uploads', 'public');
            $fileSize = $file->getSize();
            
            try {
                $parser = new DocumentParser();
                $fileText = $parser->extract(storage_path('app/public/' . $filePath));
            } catch (\Throwable $e) {
                Log::warning('File parsing failed', ['file' => $fileName, 'error' => $e->getMessage()]);
                $fileText = '';
            }
        }

        // Формируем сообщение для AI (с текстом файла, если прикреплён)
        $messageForAI = $content;
        if ($fileText) {
            $messageForAI = "[Прикреплён документ: $fileName]\n\n--- Содержимое документа ---\n$fileText\n\n--- Вопрос пользователя ---\n$content";
        }

        // === КОНТЕКСТ КВИК-ПРОМТА ===
        $promptKey = $chat->prompt_key ?? $request->input('prompt_key');
        if ($promptKey) {
            $quickPrompt = QuickPrompt::where('key', $promptKey)->where('active', true)->first();
            if ($quickPrompt && $quickPrompt->text) {
                $promptContext = "\n\n--- Контекст выбранной темы консультации ---\n"
                    . "Тема: {$quickPrompt->title}\n"
                    . "Инструкции для юриста: {$quickPrompt->text}\n"
                    . "Отвечай строго в рамках этой темы, используя инструкции выше.\n"
                    . "--- Конец контекста темы ---\n";
                $messageForAI = $promptContext . $messageForAI;
            }
        }

        // === ПАМЯТЬ МЕЖДУ ЧАТАМИ: подтягиваем контекст предыдущих консультаций ===
        try {
            if (!$isGuest) {
                $query = Chat::where('user_id', Auth::id())
                    ->where('id', '!=', $chat->id)
                    ->whereNotNull('summary');
                
                $previousChats = $query->orderByDesc('updated_at')->limit(5)->get();

                if ($previousChats->isNotEmpty()) {
                    $categoryIcons = [
                        'labor' => '⚖️', 'family' => '👨‍👩‍👧', 'housing' => '🏠',
                        'consumer' => '📝', 'traffic' => '🚗', 'court' => '🏛️', 'other' => '💼',
                    ];
                    
                    $contextLines = [];
                    foreach ($previousChats as $pc) {
                        $icon = $categoryIcons[$pc->category] ?? '💼';
                        $age = $pc->updated_at->diffForHumans();
                        $contextLines[] = "- {$icon} {$pc->summary} ({$age})";
                    }
                    
                    $contextBlock = "\n\n--- Контекст предыдущих консультаций клиента ---\n"
                        . implode("\n", $contextLines)
                        . "\n--- Конец контекста ---\n";
                    
                    $messageForAI = $contextBlock . $messageForAI;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Memory context failed', ['chat' => $chat->id, 'error' => $e->getMessage()]);
        }

        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $content,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $fileSize ?? null,
        ]);

        if (!$isGuest) {
            Auth::user()->incrementFreeMessagesUsed();
        }

        $history = $chat->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        try {
            $aiService = new TimewebAIService();
            $aiResponse = $aiService->chat($messageForAI, $history);
        } catch (\Throwable $e) {
            Log::error('Timeweb AI error: ' . $e->getMessage());
            $aiResponse = 'Извините, сервис временно перегружен. Пожалуйста, попробуйте ещё раз через минуту.';
        }

        Message::create([
            'chat_id' => $chat->id,
            'role' => 'assistant',
            'content' => $aiResponse,
        ]);

        // === ПОКАЗ РЕКЛАМЫ ===
        try {
            $userMsgCount = Message::where('chat_id', $chat->id)->where('role', 'user')->count();
            // Показываем рекламу каждые 3 сообщения пользователя (3, 6, 9...)
            if ($userMsgCount > 0 && $userMsgCount % 3 === 0) {
                $ad = Ad::where('active', true)->inRandomOrder()->first();
                if ($ad) {
                    $adContent = $ad->content;
                    if ($ad->cta_text && $ad->cta_url) {
                        $adContent .= '<div class="mt-3"><a href="' . e($ad->cta_url) . '" class="inline-block px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition">' . e($ad->cta_text) . '</a></div>';
                    }
                    Message::create([
                        'chat_id' => $chat->id,
                        'role' => 'assistant',
                        'content' => $adContent,
                        'is_ad' => true,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Ad display failed', ['chat' => $chat->id, 'error' => $e->getMessage()]);
        }

        // === АВТОКАТЕГОРИЗАЦИЯ: один раз на чат после первого ответа ===
        try {
            $messagesCount = Message::where('chat_id', $chat->id)->count();
            if ($messagesCount === 2 && empty($chat->category)) {
                $categorizePrompt = "Проанализируй диалог юриста с клиентом и определи:
1. Категорию права (одно из: labor/family/housing/consumer/traffic/court/other)
2. Краткое описание сути консультации (до 80 символов, на русском языке)

Ответь СТРОГО в формате JSON без markdown:
{\"category\": \"category_key\", \"summary\": \"краткое описание\"}

Диалог:
Клиент: {$content}
Юрист: {$aiResponse}";

                try {
                    $categorizeService = new TimewebAIService();
                    $response = trim($categorizeService->chat($categorizePrompt));
                    $response = preg_replace('/^```json\s*|```\s*$/', '', $response);
                    $decoded = json_decode($response, true);
                    if (is_array($decoded) && isset($decoded['category'])) {
                        $chat->update([
                            'category' => $decoded['category'],
                            'summary' => $decoded['summary'] ?? null,
                        ]);
                        Log::info('Chat categorized', ['chat' => $chat->id, 'category' => $decoded['category']]);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Categorization failed', ['chat' => $chat->id, 'error' => $e->getMessage()]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Categorization outer error', ['chat' => $chat->id, 'error' => $e->getMessage()]);
        }

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
            $messagesUsed = Message::whereHas('chat', function ($q) use ($guestId) {
                $q->where('guest_id', $guestId)->whereNull('user_id');
            })->where('role', 'user')->count();
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
                'prompt_key' => $request->input('prompt_key'),
            ]);
        }

        // === ОБРАБОТКА ЗАГРУЖЕННОГО ФАЙЛА ===
        $fileName = null;
        $filePath = null;
        $fileText = '';
        
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $ext = strtolower($file->getClientOriginalExtension());
            $allowed = DocumentParser::ALLOWED_EXT;
            
            if (!in_array($ext, $allowed)) {
                return response()->json(['error' => 'Недопустимый формат файла'], 400);
            }
            
            if ($file->getSize() > 10 * 1024 * 1024) {
                return response()->json(['error' => 'Файл слишком большой. Максимум 10 МБ.'], 400);
            }
            
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('uploads', 'public');
            $fileSize = $file->getSize();
            
            try {
                $parser = new DocumentParser();
                $fileText = $parser->extract(storage_path('app/public/' . $filePath));
            } catch (\Throwable $e) {
                Log::warning('File parsing failed', ['file' => $fileName, 'error' => $e->getMessage()]);
                $fileText = '';
            }
        }

        // Формируем сообщение для AI (с текстом файла, если прикреплён)
        $messageForAI = $content;
        if ($fileText) {
            $messageForAI = "[Прикреплён документ: $fileName]\n\n--- Содержимое документа ---\n$fileText\n\n--- Вопрос пользователя ---\n$content";
        }

        // === КОНТЕКСТ КВИК-ПРОМТА ===
        $promptKey = $chat->prompt_key ?? $request->input('prompt_key');
        if ($promptKey) {
            $quickPrompt = QuickPrompt::where('key', $promptKey)->where('active', true)->first();
            if ($quickPrompt && $quickPrompt->text) {
                $promptContext = "\n\n--- Контекст выбранной темы консультации ---\n"
                    . "Тема: {$quickPrompt->title}\n"
                    . "Инструкции для юриста: {$quickPrompt->text}\n"
                    . "Отвечай строго в рамках этой темы, используя инструкции выше.\n"
                    . "--- Конец контекста темы ---\n";
                $messageForAI = $promptContext . $messageForAI;
            }
        }

        // === ПАМЯТЬ МЕЖДУ ЧАТАМИ: подтягиваем контекст предыдущих консультаций ===
        try {
            if (!$isGuest) {
                $query = Chat::where('user_id', Auth::id())
                    ->where('id', '!=', $chat->id)
                    ->whereNotNull('summary');
                
                $previousChats = $query->orderByDesc('updated_at')->limit(5)->get();

                if ($previousChats->isNotEmpty()) {
                    $categoryIcons = [
                        'labor' => '⚖️', 'family' => '👨‍👩‍👧', 'housing' => '🏠',
                        'consumer' => '📝', 'traffic' => '🚗', 'court' => '🏛️', 'other' => '💼',
                    ];
                    
                    $contextLines = [];
                    foreach ($previousChats as $pc) {
                        $icon = $categoryIcons[$pc->category] ?? '💼';
                        $age = $pc->updated_at->diffForHumans();
                        $contextLines[] = "- {$icon} {$pc->summary} ({$age})";
                    }
                    
                    $contextBlock = "\n\n--- Контекст предыдущих консультаций клиента ---\n"
                        . implode("\n", $contextLines)
                        . "\n--- Конец контекста ---\n";
                    
                    $messageForAI = $contextBlock . $messageForAI;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Memory context failed', ['chat' => $chat->id, 'error' => $e->getMessage()]);
        }

        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $content,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $fileSize ?? null,
        ]);

        if (!$isGuest) {
            Auth::user()->incrementFreeMessagesUsed();
        }

        $history = $chat->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $aiService = new TimewebAIService();
        $fullResponse = '';

        return response()->stream(function () use ($aiService, $messageForAI, $history, $chat, &$fullResponse, $content) {
            try {
                foreach ($aiService->chatStream($messageForAI, $history) as $chunk) {
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

                // === ПОКАЗ РЕКЛАМЫ ===
                try {
                    $userMsgCount = Message::where('chat_id', $chat->id)->where('role', 'user')->count();
                    // Показываем рекламу каждые 3 сообщения пользователя (3, 6, 9...)
                    if ($userMsgCount > 0 && $userMsgCount % 3 === 0) {
                        $ad = Ad::where('active', true)->inRandomOrder()->first();
                        if ($ad) {
                            $adContent = $ad->content;
                            if ($ad->cta_text && $ad->cta_url) {
                                $adContent .= '<div class="mt-3"><a href="' . e($ad->cta_url) . '" class="inline-block px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition">' . e($ad->cta_text) . '</a></div>';
                            }
                            Message::create([
                                'chat_id' => $chat->id,
                                'role' => 'assistant',
                                'content' => $adContent,
                                'is_ad' => true,
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Ad display failed', ['chat' => $chat->id, 'error' => $e->getMessage()]);
                }

                // === АВТОКАТЕГОРИЗАЦИЯ: один раз на чат после первого ответа ===
                try {
                    $freshChat = Chat::find($chat->id);
                    $messagesCount = Message::where('chat_id', $chat->id)->count();
                    if ($messagesCount === 2 && empty($freshChat->category)) {
                        $categorizePrompt = "Проанализируй диалог юриста с клиентом и определи:
1. Категорию права (одно из: labor/family/housing/consumer/traffic/court/other)
2. Краткое описание сути консультации (до 80 символов, на русском языке)

Ответь СТРОГО в формате JSON без markdown:
{\"category\": \"category_key\", \"summary\": \"краткое описание\"}

Диалог:
Клиент: {$content}
Юрист: {$fullResponse}";

                        try {
                            $categorizeService = new TimewebAIService();
                            $response = trim($categorizeService->chat($categorizePrompt));
                            $response = preg_replace('/^```json\s*|```\s*$/', '', $response);
                            $decoded = json_decode($response, true);
                            if (is_array($decoded) && isset($decoded['category'])) {
                                $freshChat->update([
                                    'category' => $decoded['category'],
                                    'summary' => $decoded['summary'] ?? null,
                                ]);
                                Log::info('Chat categorized', ['chat' => $chat->id, 'category' => $decoded['category']]);
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Categorization failed', ['chat' => $chat->id, 'error' => $e->getMessage()]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Categorization outer error', ['chat' => $chat->id, 'error' => $e->getMessage()]);
                }
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
            // Считаем фактическое количество сообщений гостя по БД
            $messagesUsed = \App\Models\Message::whereHas('chat', function ($q) use ($guestId) {
                $q->where('guest_id', $guestId)->whereNull('user_id');
            })->where('role', 'user')->count();
            if ($messagesUsed >= self::FREE_LIMIT) {
                return redirect()->route('pricing')
                    ->with('error', 'Бесплатный лимит исчерпан. Зарегистрируйтесь, чтобы продолжить.');
            }
        }

        // Ищем существующий пустой чат (без сообщений)
        $emptyChat = Chat::whereDoesntHave('messages')
            ->where(function ($q) use ($isGuest, $guestId) {
                if ($isGuest) {
                    $q->where('guest_id', $guestId)->whereNull('user_id');
                } else {
                    $q->where('user_id', Auth::id());
                }
            })
            ->first();

        if ($emptyChat) {
            // Есть пустой чат — открываем его вместо создания нового
            return redirect()->route('chat.show', ['chat_id' => $emptyChat->id]);
        }

        // Нет пустых чатов — создаём новый
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
