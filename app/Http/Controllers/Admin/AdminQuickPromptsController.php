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
        return view('admin.quick-prompts.index', compact('prompts'));
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

}
