<?php

return [
    // Курс доллара для расчёта стоимости (обновляйте при необходимости)
    'usd_rate' => 90,

    // Цены за 1 млн токенов в USD (приблизительные цены Timeweb/DashScope)
    // Обновите точные значения из панели Timeweb Cloud → AI Gateway
    'prices' => [
        'deepseek/deepseek-v4-flash' => ['input' => 0.14, 'output' => 0.28],
        'deepseek/deepseek-v4-pro'   => ['input' => 0.43, 'output' => 0.86],
        'dashscope/qwen3.5-flash'    => ['input' => 0.02, 'output' => 0.20],
        'dashscope/qwen3.6-flash'    => ['input' => 0.02, 'output' => 0.20],
        'dashscope/qwen3.5-plus'     => ['input' => 0.40, 'output' => 1.20],
        'dashscope/qwen3.6-plus'     => ['input' => 0.40, 'output' => 1.20],
        'dashscope/qwen3.7-max'      => ['input' => 1.20, 'output' => 6.00],
        'gemini/gemini-3.5-flash'    => ['input' => 0.30, 'output' => 2.50],
        'anthropic/claude-sonnet-4-5' => ['input' => 3.00, 'output' => 15.00],
        'openai/gpt-5-mini'          => ['input' => 0.25, 'output' => 2.00],
    ],
];
