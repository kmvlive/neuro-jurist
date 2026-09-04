<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Сохранить/обновить голос 👍/ за сообщение ассистента
     */
    public function store(Request $request, Message $message)
    {
        $vote = (int) $request->input('vote');
        if (!in_array($vote, [1, -1], true)) {
            return response()->json(['error' => 'Некорректный голос'], 422);
        }

        // Фидбек только за ответы ассистента
        if ($message->role !== 'assistant') {
            return response()->json(['error' => 'Оценивать можно только ответы ассистента'], 422);
        }

        $userId = Auth::id();
        $guestId = Auth::check() ? null : ($request->cookie('guest_id') ?? session('guest_id'));

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

        return response()->json(['status' => 'saved', 'vote' => $vote]);
    }

    /**
     * Комментарий к 👎 (что не так с ответом)
     */
    public function comment(Request $request, Message $message)
    {
        $userId = Auth::id();
        $guestId = Auth::check() ? null : ($request->cookie('guest_id') ?? session('guest_id'));

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
    }
}
