<?php

namespace App\Services\AI;

use OpenAI;

class TimewebAIService
{
    public function chat(string $userMessage, array $history = []): string
    {
        $client = OpenAI::factory()
            ->withApiKey(env('TIMEWEB_AI_KEY'))
            ->withBaseUri('https://api.timeweb.ai/v1')
            ->make();

        $messages = [
            [
                'role' => 'system',
                'content' => 'Ты — «Нейро-юрист», профессиональный AI-ассистент по праву Российской Федерации. Отвечай понятно, структурированно и кратко. Ссылайся на законы и статьи, когда это уместно. Не выдумывай нормы права. В сложных случаях рекомендуй обратиться к живому юристу.',
            ],
        ];

        foreach ($history as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $response = $client->chat()->create([
            'model' => 'deepseek/deepseek-v4-flash',
            'messages' => $messages,
        ]);

        return $response->choices[0]->message->content;
    }
}
