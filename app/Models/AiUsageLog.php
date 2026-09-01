<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = [
        'chat_id', 'user_id', 'model', 'type',
        'prompt_tokens', 'completion_tokens', 'reasoning_tokens',
        'first_chunk_ms', 'total_ms', 'success', 'error',
    ];

    protected $casts = [
        'success' => 'boolean',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Стоимость запроса в рублях (цены в config/ai.php) */
    public function costRub(): float
    {
        $prices = config('ai.prices', []);
        $price = $prices[$this->model] ?? null;
        if (!$price || !isset($price['input'], $price['output'])) return 0.0;

        $usd = (($this->prompt_tokens ?? 0) / 1_000_000) * $price['input']
             + (($this->completion_tokens ?? 0) / 1_000_000) * $price['output'];

        return $usd * config('ai.usd_rate', 90);
    }
}
