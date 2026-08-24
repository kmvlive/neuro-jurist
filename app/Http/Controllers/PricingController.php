<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PricingController extends Controller
{
    /**
     * Показать страницу тарифов (доступно без авторизации)
     */
    public function show()
    {
        $plans = [
            [
                'name' => 'Старт',
                'price' => 0,
                'currency' => '₽',
                'period' => 'бесплатно',
                'features' => [
                    '20 бесплатных консультаций',
                    'Базовые юридические вопросы',
                    'Сохранение истории чатов',
                    'Email поддержка',
                ],
                'limitations' => [
                    'Лимит: 20 сообщений',
                    'Без приоритетной поддержки',
                ],
                'buttonText' => 'Выбрать',
                'highlighted' => false,
            ],
            [
                'name' => 'Профи',
                'price' => 990,
                'currency' => '₽',
                'period' => 'мес',
                'features' => [
                    'Безлимитные консультации',
                    'Расширенные юридические услуги',
                    'Приоритетная поддержка',
                    'Сохранение всей истории',
                    'Шаблоны документов',
                ],
                'limitations' => [],
                'buttonText' => 'Выбрать',
                'highlighted' => true,
            ],
            [
                'name' => 'Бизнес',
                'price' => 2990,
                'currency' => '₽',
                'period' => 'мес',
                'features' => [
                    'Всё из тарифа Профи',
                    'Персональный юрист',
                    'Консультации по видеосвязи',
                    'Составление документов',
                    'Представительство в суде',
                    'API доступ',
                ],
                'limitations' => [],
                'buttonText' => 'Выбрать',
                'highlighted' => false,
            ],
        ];

        return view('pricing.show', compact('plans'));
    }

    /**
     * Обработка выбора тарифа
     */
    public function select(Request $request, string $plan)
    {
        $validPlans = ['start', 'profi', 'business'];
        
        if (!in_array($plan, $validPlans)) {
            abort(404);
        }

        // Для гостей - редирект на регистрацию
        if (!auth()->check()) {
            return redirect()->route('register')
                ->with('info', 'Зарегистрируйтесь, чтобы выбрать тариф "' . ucfirst($plan) . '"');
        }

        // Для авторизованных - пока заглушка
        return redirect()->back()
            ->with('info', 'Оплата будет подключена в следующем обновлении');
    }
}
