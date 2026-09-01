<?php

namespace App\Services\AI;

use OpenAI;
use App\Models\Setting;

class TimewebAIService
{
    /** Метрики последнего запроса: модель, токены, время */
    public ?array $lastUsage = null;

    public function chat(string $userMessage, array $history = [], ?string $topic = null): string
    {
        $client = $this->makeClient();
        $messages = $this->buildMessages($userMessage, $history, $topic);
        [$model, $params] = $this->buildParams($messages);

        $start = microtime(true);
        $response = $client->chat()->create($params);

        $this->lastUsage = [
            'model' => $model,
            'prompt_tokens' => $response->usage->promptTokens ?? null,
            'completion_tokens' => $response->usage->completionTokens ?? null,
            'reasoning_tokens' => $response->usage->completionTokensDetails->reasoningTokens ?? null,
            'first_chunk_ms' => null,
            'total_ms' => (int) round((microtime(true) - $start) * 1000),
        ];

        return $response->choices[0]->message->content;
    }

    public function chatStream(string $userMessage, array $history = [], ?string $topic = null): \Generator
    {
        $client = $this->makeClient();
        $messages = $this->buildMessages($userMessage, $history, $topic);
        [$model, $params] = $this->buildParams($messages);

        $this->lastUsage = [
            'model' => $model,
            'prompt_tokens' => null,
            'completion_tokens' => null,
            'reasoning_tokens' => null,
            'first_chunk_ms' => null,
            'total_ms' => null,
        ];

        $start = microtime(true);
        $firstChunkAt = null;

        $stream = $client->chat()->createStreamed($params);

        foreach ($stream as $response) {
            if ($firstChunkAt === null) {
                $firstChunkAt = microtime(true);
            }
            // Провайдер возвращает usage в последнем чанке стрима
            if ($response->usage?->completionTokens) {
                $this->lastUsage['prompt_tokens'] = $response->usage->promptTokens ?? null;
                $this->lastUsage['completion_tokens'] = $response->usage->completionTokens ?? null;
                $this->lastUsage['reasoning_tokens'] = $response->usage->completionTokensDetails->reasoningTokens ?? null;
            }
            $content = $response->choices[0]->delta->content ?? '';
            if ($content !== '') {
                yield $content;
            }
        }

        $this->lastUsage['first_chunk_ms'] = $firstChunkAt ? (int) round(($firstChunkAt - $start) * 1000) : null;
        $this->lastUsage['total_ms'] = (int) round((microtime(true) - $start) * 1000);
    }

    private function makeClient()
    {
        return OpenAI::factory()
            ->withApiKey(env('TIMEWEB_AI_KEY'))
            ->withBaseUri('https://api.timeweb.ai/v1')
            ->make();
    }

    private function buildParams(array $messages): array
    {
        $model = Setting::get('ai_model', env('TIMEWEB_AI_MODEL', 'deepseek/deepseek-v4-flash'));
        $params = ['model' => $model, 'messages' => $messages];
        // Для Qwen3+ отключаем режим "размышлений" (экономит 15-20 секунд)
        if (preg_match('/^dashscope\/qwen3/i', $model)) {
            $params['enable_thinking'] = false;
        }
        return [$model, $params];
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
