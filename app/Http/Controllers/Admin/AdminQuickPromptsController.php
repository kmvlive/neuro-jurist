<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuickPrompt;
use App\Models\PromptCategory;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdminQuickPromptsController extends Controller
{
    public function index()
    {
        $prompts = QuickPrompt::with('categories')->orderBy('sort_order')->get();
        
        // Добавляем статистику отзывов для каждого промпта
        $feedbackStats = \App\Models\MessageFeedback::selectRaw('
            c.prompt_key,
            COUNT(*) as total,
            SUM(CASE WHEN message_feedbacks.vote = 1 THEN 1 ELSE 0 END) as up,
            SUM(CASE WHEN message_feedbacks.vote = -1 THEN 1 ELSE 0 END) as down
        ')
        ->join('messages as m', 'm.id', '=', 'message_feedbacks.message_id')
        ->join('chats as c', 'c.id', '=', 'm.chat_id')
        ->whereIn('c.prompt_key', $prompts->pluck('key'))
        ->groupBy('c.prompt_key')
        ->get()
        ->keyBy('prompt_key');
        
        return view('admin.quick-prompts.index', compact('prompts', 'feedbackStats'));
    }

    public function create()
    {
        $categories = PromptCategory::with('parent')->orderByRaw('COALESCE(parent_id, id) ASC, parent_id IS NOT NULL ASC, sort_order ASC')->get();
        return view('admin.quick-prompts.form', ['prompt' => null, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'key' => 'required|string|max:50|unique:quick_prompts,key|regex:/^[a-z0-9_]+$/',
            'icon' => 'required|string|max:10',
            'text' => 'nullable|string|max:5000',
            'active' => 'in:0,1',
            'show_in_chat' => 'in:0,1',
            'sort_order' => 'integer',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:prompt_categories,id',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_text' => 'nullable|string',
            'example_questions' => 'nullable|string',
        ]);
        $categories = $data['categories'] ?? [];
        unset($data['categories']);
        if (isset($data['example_questions'])) {
            $data['example_questions'] = array_values(array_filter(array_map('trim', explode("\n", $data['example_questions']))));
        }
        $prompt = QuickPrompt::create($data);
        $prompt->categories()->sync($categories);
        return redirect()->route('admin.quick-prompts.index')->with('success', 'Промпт создан');
    }

    public function edit(QuickPrompt $quickPrompt)
    {
        $categories = PromptCategory::with('parent')->orderByRaw('COALESCE(parent_id, id) ASC, parent_id IS NOT NULL ASC, sort_order ASC')->get();
        return view('admin.quick-prompts.form', ['prompt' => $quickPrompt, 'categories' => $categories]);
    }

    public function update(Request $request, QuickPrompt $quickPrompt)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'key' => 'required|string|max:50|unique:quick_prompts,key,' . $quickPrompt->id . '|regex:/^[a-z0-9_]+$/',
            'icon' => 'required|string|max:10',
            'text' => 'nullable|string|max:5000',
            'active' => 'in:0,1',
            'show_in_chat' => 'in:0,1',
            'sort_order' => 'integer',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:prompt_categories,id',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_text' => 'nullable|string',
            'example_questions' => 'nullable|string',
        ]);
        $categories = $data['categories'] ?? [];
        unset($data['categories']);
        if (isset($data['example_questions'])) {
            $data['example_questions'] = array_values(array_filter(array_map('trim', explode("\n", $data['example_questions']))));
        }
        $quickPrompt->update($data);
        $quickPrompt->categories()->sync($categories);
        return redirect()->route('admin.quick-prompts.index')->with('success', 'Промпт обновлён');
    }

    public function generateSeo(QuickPrompt $quickPrompt)
    {
        try {
            $ai = new \App\Services\AI\TimewebAIService();
            $request = "Ты — SEO-копирайтер для юридического AI-сервиса. Тема консультации: «{$quickPrompt->title}».\n"
                . "Инструкция для юриста: " . ($quickPrompt->text ?: 'Базовая консультация по теме') . "\n\n"
                . "Сгенерируй СТРОГО в формате JSON без markdown:\n"
                . "{\n  \"title\": \"SEO-заголовок (до 70 символов, с ключевыми словами)\",\n  \"description\": \"Мета-описание (150-160 символов)\",\n  \"text\": \"Экспертный текст (2-3 абзаца, со ссылками на статьи законов)\",\n  \"questions\": [\"Вопрос 1\", \"Вопрос 2\", \"Вопрос 3\", \"Вопрос 4\", \"Вопрос 5\"]\n}";
            $response = trim($ai->chat($request));
            $response = preg_replace('/^```json\s*|```\s*$/', '', $response);
            $data = json_decode($response, true);
            if (is_array($data)) {
                $quickPrompt->update([
                    'seo_title' => $data['title'] ?? $quickPrompt->seo_title,
                    'seo_description' => $data['description'] ?? $quickPrompt->seo_description,
                    'seo_text' => $data['text'] ?? $quickPrompt->seo_text,
                    'example_questions' => $data['questions'] ?? $quickPrompt->example_questions,
                ]);
                return redirect()->back()->with('success', '✨ SEO-контент сгенерирован');
            }
            return redirect()->back()->with('error', 'AI вернул некорректный JSON, попробуйте ещё раз');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SEO generation failed', ['prompt' => $quickPrompt->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Ошибка генерации: ' . $e->getMessage());
        }
    }

    public function destroy(QuickPrompt $quickPrompt)
    {
        $quickPrompt->delete();
        return redirect()->route('admin.quick-prompts.index')->with('success', 'Промпт удалён');
    }

    public function editAd(QuickPrompt $quickPrompt)
    {
        $ad = $quickPrompt->ad;
        return view('admin.quick-prompts.ad-form', compact('quickPrompt', 'ad'));
    }

    public function updateAd(Request $request, QuickPrompt $quickPrompt)
    {
        $data = $request->validate([
            'content' => 'required|string',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        if ($quickPrompt->ad) {
            $quickPrompt->ad->update($data);
        } else {
            $data['quick_prompt_id'] = $quickPrompt->id;
            Ad::create($data);
        }

        return redirect()->route('admin.quick-prompts.index')->with('success', 'Реклама сохранена');
    }

    public function toggleAllAds(Request $request)
    {
        $action = $request->input('action');
        if ($action === 'disable') {
            Ad::query()->update(['active' => false]);
            $msg = 'Вся реклама отключена';
        } else {
            Ad::query()->update(['active' => true]);
            $msg = 'Вся реклама включена';
        }
        return redirect()->route('admin.quick-prompts.index')->with('success', $msg);
    }


    /**
     * 🤖 Улучшение промпта на основе фидбека пользователей
     */
    public function improvePrompt(QuickPrompt $quickPrompt)
    {
        \Illuminate\Support\Facades\Log::info('Improve prompt called', [
            'prompt_id' => $quickPrompt->id,
            'user_id' => auth()->id(),
            'has_csrf' => request()->header('X-CSRF-TOKEN') ? 'yes' : 'no',
        ]);
        
        try {
            $feedbacks = \App\Models\MessageFeedback::whereHas('message.chat', function($q) use ($quickPrompt) {
                    $q->where('prompt_key', $quickPrompt->key);
                })
                ->where('vote', -1)
                ->with(['message' => function($q) {
                    $q->with(['chat.messages' => function($q) {
                        $q->where('role', 'user')->orderBy('created_at', 'desc');
                    }]);
                }])
                ->latest()
                ->limit(20)
                ->get();

            $positiveFeedbacks = \App\Models\MessageFeedback::whereHas('message.chat', function($q) use ($quickPrompt) {
                    $q->where('prompt_key', $quickPrompt->key);
                })
                ->where('vote', 1)
                ->count();

            $totalFeedbacks = $feedbacks->count() + $positiveFeedbacks;

            $issues = [];
            foreach ($feedbacks as $fb) {
                $comment = trim($fb->comment ?? '');
                $answer = $fb->message ? \Illuminate\Support\Str::limit($fb->message->content, 300) : '';
                
                $question = '';
                if ($fb->message && $fb->message->chat) {
                    $userMsg = $fb->message->chat->messages
                        ->where('role', 'user')
                        ->where('created_at', '<', $fb->message->created_at)
                        ->sortByDesc('created_at')
                        ->first();
                    $question = $userMsg ? \Illuminate\Support\Str::limit($userMsg->content, 200) : '';
                }
                
                $issues[] = [
                    'question' => $question,
                    'answer' => $answer,
                    'comment' => $comment ?: '(комментарий не указан)',
                ];
            }

            // Если нет отзывов, генерируем улучшения на основе лучших практик
            $hasFeedback = !empty($issues) || $totalFeedbacks >= 3;

            $issuesText = '';
            foreach ($issues as $i => $issue) {
                $issuesText .= "\n\n### Отзыв " . ($i + 1) . ":\n";
                if ($issue['question']) $issuesText .= "**Вопрос:** {$issue['question']}\n";
                if ($issue['answer'])   $issuesText .= "**Ответ AI (проблемный):** {$issue['answer']}\n";
                $issuesText .= "**Замечание пользователя:** {$issue['comment']}";
            }

            $currentText = $quickPrompt->text ?: '(базовая консультация по теме)';
            
            // Формируем блок замечаний
            $issuesBlock = trim($issuesText) ?: '(отзывов пока нет — предложи улучшения на основе лучших практик для юридических консультаций)';

            $request = "Ты — эксперт по улучшению системных промптов для юридических AI-консультантов.

## Контекст
- **Тема консультации:** «{$quickPrompt->title}»
- **Текущий промпт (инструкция для AI):**
{$currentText}

## Статистика фидбека
- Всего оценок: {$totalFeedbacks}
- Полезных (👍): {$positiveFeedbacks}
- Плохих (👎): {$feedbacks->count()}

## Замечания пользователей (плохие ответы + их комментарии)
{$issuesBlock}

## Задача
Проанализируй замечания и улучши промпт так, чтобы:
1. Учесть конкретные жалобы пользователей
2. Добавить недостающие элементы (примеры, ссылки на законы, структуру)
3. Сохранить юридическую точность
4. Ответы стали более конкретными и полезными

## Формат ответа (СТРОГО JSON без markdown)
{
  \"improved_text\": \"Улучшенный текст промпта (2000-5000 символов)\",
  \"changes_summary\": \"Краткое описание 3-5 ключевых изменений (1-2 предложения)\",
  \"issues_addressed\": [\"Проблема 1\", \"Проблема 2\", \"Проблема 3\"]
}";

            $ai = new \App\Services\AI\TimewebAIService();
            $response = trim($ai->chat($request));
            $response = preg_replace('/^```json\s*|```\s*$/', '', $response);
            $response = preg_replace('/^```\w*\s*|\s*```\s*$/', '', $response);
            
            $data = json_decode($response, true);
            
            if (!is_array($data) || empty($data['improved_text'])) {
                \Illuminate\Support\Facades\Log::warning('Improve prompt failed: bad JSON', ['response' => $response]);
                return response()->json([
                    'success' => false,
                    'message' => 'AI вернул некорректный формат. Попробуйте ещё раз.'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'current_text' => $currentText,
                'improved_text' => $data['improved_text'],
                'changes_summary' => $data['changes_summary'] ?? '',
                'issues_addressed' => $data['issues_addressed'] ?? [],
                'stats' => [
                    'total' => $totalFeedbacks,
                    'up' => $positiveFeedbacks,
                    'down' => $feedbacks->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Improve prompt error', [
                'prompt' => $quickPrompt->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Применить улучшенный промпт
     */
    public function applyImprovedPrompt(Request $request, QuickPrompt $quickPrompt)
    {
        $data = $request->validate([
            'improved_text' => 'required|string',
        ]);

        $quickPrompt->update(['text' => $data['improved_text']]);

        \Illuminate\Support\Facades\Log::info('Prompt improved via AI', [
            'prompt_id' => $quickPrompt->id,
            'prompt_key' => $quickPrompt->key,
        ]);

        return redirect()
            ->route('admin.quick-prompts.edit', $quickPrompt)
            ->with('success', '✨ Промпт улучшен и сохранён! Теперь AI будет отвечать лучше по теме "' . $quickPrompt->title . '"');
    }
}

