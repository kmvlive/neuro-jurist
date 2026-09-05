<?php

namespace App\Console\Commands;

use App\Models\QuickPrompt;
use App\Services\AI\TimewebAIService;
use Illuminate\Console\Command;

class GenerateBulkSEO extends Command
{
    protected $signature = 'app:generate-bulk-seo {--limit=10 : Максимум промптов за раз} {--dry-run}';
    protected $description = 'Массовая генерация SEO для промптов без seo_title';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        $prompts = QuickPrompt::whereNull('seo_title')
            ->where('active', true)
            ->limit($limit)
            ->get();

        if ($prompts->isEmpty()) {
            $this->info('Все промпты уже с SEO!');
            return;
        }

        $this->info("Найдено {$prompts->count()} промптов без SEO. Генерирую...");
        
        $ai = new TimewebAIService();
        $success = 0;
        $failed = 0;

        foreach ($prompts as $prompt) {
            $this->newLine();
            $this->info("→ {$prompt->icon} {$prompt->title} ({$prompt->key})");

            if ($dryRun) {
                $this->info("  [DRY-RUN] Пропускаю");
                continue;
            }

            try {
                $request = "Ты — SEO-копирайтер для юридического AI-сервиса. Тема консультации: «{$prompt->title}».\n"
                    . "Инструкция для юриста: " . ($prompt->text ?: 'Базовая консультация по теме') . "\n\n"
                    . "Сгенерируй СТРОГО в формате JSON без markdown:\n"
                    . "{\n  \"title\": \"SEO-заголовок (до 70 символов, с ключевыми словами)\",\n  \"description\": \"Мета-описание (150-160 символов, призыв к действию)\",\n  \"text\": \"Экспертный текст для страницы (2-3 абзаца, со ссылками на статьи законов РФ, 800-1200 символов)\",\n  \"questions\": [\"Вопрос 1\", \"Вопрос 2\", \"Вопрос 3\", \"Вопрос 4\", \"Вопрос 5\"]\n}";
                
                $response = trim($ai->chat($request));
                $response = preg_replace('/^```json\s*|```\s*$/', '', $response);
                $response = preg_replace('/^```\w*\s*|\s*```\s*$/', '', $response);
                
                $data = json_decode($response, true);
                
                if (!is_array($data) || empty($data['title'])) {
                    $this->error("  ✗ AI вернул некорректный JSON");
                    $failed++;
                    continue;
                }

                $prompt->update([
                    'seo_title' => $data['title'],
                    'seo_description' => $data['description'] ?? '',
                    'seo_text' => $data['text'] ?? '',
                    'example_questions' => $data['questions'] ?? [],
                ]);

                $this->info("  ✓ Title: {$data['title']}");
                $this->line("  ✓ Description: " . mb_substr($data['description'] ?? '', 0, 80) . '...');
                $success++;

                // Небольшая пауза, чтобы не перегружать AI
                sleep(2);

            } catch (\Throwable $e) {
                $this->error("  ✗ Ошибка: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("=== Готово ===");
        $this->info("Успешно: $success");
        if ($failed) $this->warn("Ошибок: $failed");
        $this->info("Осталось без SEO: " . QuickPrompt::whereNull('seo_title')->count());
    }
}
