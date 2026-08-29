<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Services\AI\TimewebAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = DocumentTemplate::getActive();
        return view('templates.index', compact('templates'));
    }

    public function show(string $key)
    {
        $template = DocumentTemplate::findByKey($key);
        if (!$template || !$template->active) {
            abort(404);
        }
        return view('templates.show', compact('template'));
    }

    public function generate(Request $request, string $key)
    {
        $template = DocumentTemplate::findByKey($key);
        if (!$template || !$template->active) {
            abort(404);
        }

        // Проверяем лимит для гостей и бесплатных пользователей
        $isGuest = !Auth::check();
        if ($isGuest) {
            return redirect()->route('register')->with('error', 'Зарегистрируйтесь, чтобы генерировать документы.');
        }

        $user = Auth::user();
        if (!$user->canSendMessages()) {
            return redirect()->route('pricing')->with('error', 'Достигнут лимит сообщений. Выберите тариф.');
        }

        // Валидация ответов
        $answers = [];
        foreach ($template->questions as $q) {
            $answers[$q['key']] = $request->input($q['key'], '');
            if (!empty($q['required']) && empty($answers[$q['key']])) {
                return back()->with('error', 'Заполните обязательное поле: ' . $q['label'])->withInput();
            }
        }

        // Формируем промпт
        $prompt = $template->prompt_template;
        foreach ($answers as $k => $v) {
            $prompt = str_replace('{' . $k . '}', $v, $prompt);
        }

        // Генерируем документ через AI
        $aiService = new TimewebAIService();
        try {
            $document = $aiService->generateText($prompt);
        } catch (\Throwable $e) {
            return back()->with('error', 'Ошибка генерации. Попробуйте ещё раз.')->withInput();
        }

        // Списываем сообщение
        $user->incrementFreeMessagesUsed();

        return view('templates.result', compact('template', 'document', 'answers'));
    }
}
