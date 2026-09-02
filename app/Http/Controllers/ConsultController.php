<?php

namespace App\Http\Controllers;

use App\Models\QuickPrompt;
use Illuminate\Http\Request;

class ConsultController extends Controller
{
    public function show(string $key)
    {
        $prompt = QuickPrompt::where('key', $key)->where('active', true)->firstOrFail();

        // SEO title: либо из seo_title, либо из title + ключевые слова
        $seoTitle = $prompt->seo_title ?: $prompt->title . ' — консультация юриста онлайн';
        $seoDescription = $prompt->seo_description ?: 'Бесплатная консультация по теме «' . $prompt->title . '». Задайте вопрос юристу и получите ответ за несколько минут.';

        // Связанные темы (3 случайные, кроме текущей)
        $relatedPrompts = QuickPrompt::where('active', true)
            ->where('show_in_chat', true)
            ->where('key', '!=', $key)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        return view('consult.show', compact('prompt', 'seoTitle', 'seoDescription', 'relatedPrompts'));
    }

    public function index()
    {
        $prompts = QuickPrompt::where('active', true)
            ->whereNotNull('seo_title')
            ->orderBy('sort_order')
            ->get();

        return view('consult.index', compact('prompts'));
    }
}
