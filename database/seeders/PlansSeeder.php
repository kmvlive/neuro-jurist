<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'key' => 'start',
                'name' => 'Старт',
                'price' => 0,
                'period' => 'бесплатно',
                'currency' => '₽',
                'features' => [
                    '20 бесплатных консультаций',
                    'Базовые юридические вопросы',
                    'Сохранение истории чатов',
                    'Email поддержка',
                ],
                'limitations' => ['Лимит: 20 сообщений', 'Без приоритетной поддержки'],
                'highlighted' => false,
                'button_text' => 'Выбрать',
                'sort_order' => 0,
            ],
            [
                'key' => 'profi',
                'name' => 'Профи',
                'price' => 990,
                'period' => 'мес',
                'currency' => '₽',
                'features' => [
                    'Безлимитные консультации',
                    'Расширенные юридические услуги',
                    'Приоритетная поддержка',
                    'Сохранение всей истории',
                    'Шаблоны документов',
                ],
                'limitations' => [],
                'highlighted' => true,
                'button_text' => 'Купить',
                'sort_order' => 1,
            ],
            [
                'key' => 'business',
                'name' => 'Бизнес',
                'price' => 2990,
                'period' => 'мес',
                'currency' => '₽',
                'features' => [
                    'Всё из тарифа Профи',
                    'Персональный юрист',
                    'Консультации по видеосвязи',
                    'Составление документов',
                    'Представительство в суде',
                    'API доступ',
                ],
                'limitations' => [],
                'highlighted' => false,
                'button_text' => 'Купить',
                'sort_order' => 2,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['key' => $plan['key']], $plan);
        }

        Plan::clearCache();
    }
}
