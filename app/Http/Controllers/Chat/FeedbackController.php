<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    /**
     * Сохранить/обновить голос 👍/👎 за сообщение ассистента
     */
    public function store(Request $request, Message $message)
    {
        try {
            $vote = (int) $request->input('vote');
            if (!in_array($vote, [1, -1], true)) {
                return response()->json(['error' => 'Некорректный голос'], 422);
            }

            // Фидбек только за ответы ассистента
            if ($message->role !== 'assistant') {
                return response()->json(['error' => 'Оценивать можно только ответы ассистента'], 422);
            }

            $userId = Auth::id();
            // Читаем guest_id из той же cookie, что и ChatController
            $guestId = Auth::check() ? null : $request->cookie('nj_guest_id');

            // Проверка: сообщение должно принадлежать чату пользователя
            $chat = $message->chat;
            if (!$chat) {
                return response()->json(['error' => 'Чат не найден'], 404);
            }

            if ($userId) {
                if ($chat->user_id !== $userId) {
                    return response()->json(['error' => 'Нет доступа к этому сообщению'], 403);
                }
            } else {
                if (!$guestId || $chat->guest_id !== $guestId || $chat->user_id !== null) {
                    return response()->json(['error' => 'Нет доступа к этому сообщению'], 403);
                }
            }

            if (!$userId && !$guestId) {
                return response()->json(['error' => 'Не удалось определить пользователя'], 422);
            }

            $query = MessageFeedback::where('message_id', $message->id);
            $userId
                ? $query->where('user_id', $userId)
                : $query->where('guest_id', $guestId);

            $feedback = $query->first();

            if ($feedback && $feedback->vote === $vote) {
                // Повторный клик по тому же — снимаем голос
                $feedback->delete();
                return response()->json(['status' => 'removed', 'vote' => null]);
            }

            $feedback
                ? $feedback->update(['vote' => $vote])
                : MessageFeedback::create([
                    'message_id' => $message->id,
                    'vote' => $vote,
                    'user_id' => $userId,
                    'guest_id' => $guestId,
                ]);

            Log::info('Feedback saved', [
                'message_id' => $message->id,
                'vote' => $vote,
                'user_id' => $userId,
                'guest_id' => $guestId ? 'set' : null,
            ]);

            return response()->json(['status' => 'saved', 'vote' => $vote]);
        } catch (\Throwable $e) {
            Log::error('Feedback error: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка сервера: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Комментарий к 👎 (что не так с ответом)
     */
    public function comment(Request $request, Message $message)
    {
        try {
            $userId = Auth::id();
            $guestId = Auth::check() ? null : $request->cookie('nj_guest_id');

            $query = MessageFeedback::where('message_id', $message->id);
            $userId
                ? $query->where('user_id', $userId)
                : $query->where('guest_id', $guestId);

            $feedback = $query->first();
            if (!$feedback) {
                return response()->json(['error' => 'Сначала поставьте оценку'], 422);
            }

            $feedback->update(['comment' => trim((string) $request->input('comment')) ?: null]);

            return response()->json(['status' => 'saved']);
        } catch (\Throwable $e) {
            Log::error('Feedback comment error: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка сервера: ' . $e->getMessage()], 500);
        }
    }
}
