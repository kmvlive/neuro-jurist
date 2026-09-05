<?php

namespace App\Console\Commands;

use App\Models\QuickPrompt;
use App\Services\AI\TimewebAIService;
use Illuminate\Console\Command;

class GenerateFAQAnswers extends Command
{
    protected $signature = 'faq:generate {--limit=50 : Максимум промптов за раз}';
    protected $description = 'Генерирует ответы на FAQ для промптов без example_answers';

    public function handle()
    {
        $limit = (int) $this->option('limit');

        $prompts = QuickPrompt::whereNotNull('example_questions')
            ->where('example_questions', '!=', '[]')
            ->where(function($q) {
                $q->whereNull('example_answers')
                  ->orWhere('example_answers', '=', '[]')
                  ->orWhere('example_answers', '=', 'null');
            })
            ->limit($limit)
            ->get();

        if ($prompts->isEmpty()) {
            $this->info('Все промпты уже с ответами на FAQ!');
            return;
        }

        $this->info("Найдено {$prompts->count()} промптов без ответов на FAQ. Генерирую...");
        
        $ai = new TimewebAIService();
        $success = 0;

        foreach ($prompts as $prompt) {
            $questions = $prompt->example_questions;
            if (!is_array($questions) || empty($questions)) {
                continue;
            }

            $this->info("→ {$prompt->icon} {$prompt->title}");

            try {
                $questionsText = implode("\n", array_map(fn($q, $i) => ($i+1) . ". " . $q, $questions, array_keys($questions)));
                
                $request = "Ты — юрист-эксперт. Тема консультации: «{$prompt->title}».\n\n"
                    . "Вопросы пользователей:\n{$questionsText}\n\n"
                    . "Сгенерируй краткие ответы (2-3 предложения, 150-250 символов каждый) СТРОГО в JSON без markdown:\n"
                    . "[\"Ответ 1\", \"Ответ 2\", \"Ответ 3\", \"Ответ 4\", \"Ответ 5\"]";

                $response = trim($ai->chat($request));
                $response = preg_replace('/^```(?:json)?\s*|\s*```\s*$/i', '', $response);

                $answers = json_decode($response, true);

                if (!is_array($answers) || count($answers) !== count($questions)) {
                    $this->error("  ✗ AI вернул {$count($answers)} ответов вместо " . count($questions));
                    continue;
                }

                $prompt->update(['example_answers' => $answers]);
                $this->info("  ✓ Сгенерировано " . count($answers) . " ответов");
                $success++;

                sleep(2);
            } catch (\Throwable $e) {
                $this->error("  ✗ Ошибка: " . $e->getMessage());
            }
        }

        $this->info("\nГотово! Успешно: $success");
        $remaining = QuickPrompt::whereNotNull('example_questions')
            ->where('example_questions', '!=', '[]')
            ->where(function($q) {
                $q->whereNull('example_answers')
                  ->orWhere('example_answers', '=', '[]')
                  ->orWhere('example_answers', '=', 'null');
            })
            ->count();
        $this->info("Осталось без ответов: $remaining");
    }
}
