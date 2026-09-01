<?php

namespace App\Services\AI;

use OpenAI;
use App\Models\Setting;

class TimewebAIService
{
    public function chat(string $userMessage, array $history = [], ?string $topic = null): string
    {
        $client = OpenAI::factory()
            ->withApiKey(env('TIMEWEB_AI_KEY'))
            ->withBaseUri('https://api.timeweb.ai/v1')
            ->make();

        $messages = $this->buildMessages($userMessage, $history, $topic);

        $model = Setting::get('ai_model', env('TIMEWEB_AI_MODEL', 'deepseek/deepseek-v4-flash'));
        $params = [
            'model' => $model,
            'messages' => $messages,
        ];
        if (preg_match('/^dashscope\/qwen3/i', $model)) {
            $params['enable_thinking'] = false;
        }
        
        $response = $client->chat()->create($params);

        return $response->choices[0]->message->content;
    }

    public function chatStream(string $userMessage, array $history = [], ?string $topic = null): \Generator
    {
        $client = OpenAI::factory()
            ->withApiKey(env('TIMEWEB_AI_KEY'))
            ->withBaseUri('https://api.timeweb.ai/v1')
            ->make();

        $messages = $this->buildMessages($userMessage, $history, $topic);

        $model = Setting::get('ai_model', env('TIMEWEB_AI_MODEL', 'deepseek/deepseek-v4-flash'));
        
        // Для Qwen3+ отключаем режим "размышлений" (он занимает 15-20 секунд)
        // Поддерживают параметр enable_thinking: Qwen 3, 3.5, 3.6, 3.7
        $params = [
            'model' => $model,
            'messages' => $messages,
        ];
        if (preg_match('/^dashscope\/qwen3/i', $model)) {
            $params['enable_thinking'] = false;
        }
        
        $stream = $client->chat()->createStreamed($params);

        foreach ($stream as $response) {
            $content = $response->choices[0]->delta->content ?? '';
            if ($content !== '') {
                yield $content;
            }
        }
    }

    private function buildMessages(string $userMessage, array $history, ?string $topic): array
    {
        $systemPrompt = 'Ты — «Нейро-юрист», профессиональный AI-ассистент по праву Российской Федерации. Отвечай понятно, структурированно и кратко. Ссылайся на законы и статьи, когда это уместно. Не выдумывай нормы права. В сложных случаях рекомендуй обратиться к живому юристу.';

        if ($topic) {
            $systemPrompt .= "\n\nВажный контекст: клиент обратился по теме «{$topic}». Отвечай строго в рамках этой темы, учитывай специфику. Если вопрос клиента выходит за рамки темы — мягко верни его к теме или кратко ответь, но напомни, что изначально обсуждается «{$topic}».";
        }

        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ],
        ];

        foreach ($history as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }
}
