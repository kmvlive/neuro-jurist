<?php

namespace App\Console\Commands;

use App\Models\QuickPrompt;
use App\Services\AI\TimewebAIService;
use Illuminate\Console\Command;

class GenerateSEO extends Command
{
    protected $signature = 'seo:generate {key? : Ключ конкретного промта} {--force : Перезаписать существующие}';
    protected $description = 'Генерирует SEO-контент для квик-промтов через AI';

    public function handle()
    {
        $key = $this->argument('key');
        $force = $this->option('force');

        $query = QuickPrompt::where('active', true);
        if ($key) {
            $query->where('key', $key);
        }

        $prompts = $query->get();
        if ($prompts->isEmpty()) {
            $this->error('Промты не найдены');
            return 1;
        }

        $ai = new TimewebAIService();

        foreach ($prompts as $prompt) {
            if (!$force && $prompt->seo_title && $prompt->seo_description && $prompt->seo_text && $prompt->example_questions) {
                $this->line("⏭️  Пропуск {$prompt->key} (уже заполнено)");
                continue;
            }

            $this->info("✨ Генерирую SEO для «{$prompt->title}»...");

            $request = "Ты — SEO-копирайтер для юридического AI-сервиса. Тема консультации: «{$prompt->title}».\n"
                . "Инструкция для юриста: " . ($prompt->text ?: 'Базовая консультация по теме') . "\n\n"
                . "Сгенерируй СТРОГО в формате JSON без markdown:\n"
                . "{\n"
                . "  \"title\": \"SEO-заголовок для страницы (до 70 символов, с ключевыми словами, без кавычек)\",\n"
                . "  \"description\": \"Мета-описание для сниппета (150-160 символов, привлекательное, с призывом)\",\n"
                . "  \"text\": \"Экспертный текст для страницы (2-3 абзаца, 1500-2500 символов, со ссылками на статьи законов)\",\n"
                . "  \"questions\": [\"Вопрос 1\", \"Вопрос 2\", \"Вопрос 3\", \"Вопрос 4\", \"Вопрос 5\"]\n"
                . "}\n"
                . "Вопросы должны быть теми, что реально задают пользователи по этой теме.";

            try {
                $response = trim($ai->chat($request));
                $response = preg_replace('/^```json\s*|```\s*$/', '', $response);
                $data = json_decode($response, true);

                if (!is_array($data)) {
                    $this->error("Не удалось распарсить JSON для {$prompt->key}");
                    continue;
                }

                $prompt->update([
                    'seo_title' => $data['title'] ?? $prompt->seo_title,
                    'seo_description' => $data['description'] ?? $prompt->seo_description,
                    'seo_text' => $data['text'] ?? $prompt->seo_text,
                    'example_questions' => $data['questions'] ?? $prompt->example_questions,
                ]);

                $this->info("✅ {$prompt->key}: {$data['title']}");
            } catch (\Throwable $e) {
                $this->error("Ошибка для {$prompt->key}: " . $e->getMessage());
            }
        }

        $this->info('Готово!');
        return 0;
    }
}
