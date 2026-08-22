<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class TimewebAIService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.timeweb_ai.api_key');
        $this->apiUrl = config('services.timeweb_ai.api_url', 'https://api.timeweb.ai');
    }

    /**
     * Отправить запрос к AI для юридической консультации
     */
    public function getLegalAdvice(string $message, array $context = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->post("{$this->apiUrl}/v1/chat/completions", [
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Вы — профессиональный юрист-консультант. Отвечайте на вопросы четко, профессионально, с ссылками на законодательство РФ.',
                ],
                ...$context,
                [
                    'role' => 'user',
                    'content' => $message,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ]);

        if ($response->failed()) {
            throw new \Exception('Ошибка при получении ответа от AI: ' . $response->body());
        }

        $data = $response->json();

        return [
            'message' => $data['choices'][0]['message']['content'] ?? '',
            'usage' => $data['usage'] ?? [],
        ];
    }

    /**
     * Анализ документа
     */
    public function analyzeDocument(string $documentText, string $analysisType = 'general'): array
    {
        $prompts = [
            'general' => 'Проанализируйте данный документ и укажите на потенциальные юридические риски.',
            'contract' => 'Проверьте договор на соответствие законодательству РФ и укажите на спорные моменты.',
            'claim' => 'Оцените правовую обоснованность данной претензии и дайте рекомендации.',
        ];

        $prompt = $prompts[$analysisType] ?? $prompts['general'];

        return $this->getLegalAdvice("{$prompt}\n\nТекст документа:\n{$documentText}");
    }
}
