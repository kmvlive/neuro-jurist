<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\PromoCode;
use App\Services\Payment\TBankPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PricingController extends Controller
{
    private const PLAN_ORDER = ['start' => 0, 'profi' => 1, 'business' => 2, 'week' => 0, 'premium' => 2];

    public function show()
    {
        return view('pricing.show', ['plans' => Plan::getActivePlans()]);
    }

    public function select(Request $request, string $planKey)
    {
        $plan = Plan::where('key', $planKey)->where('is_active', true)->first();

        if (!$plan) {
            abort(404);
        }

        if (!auth()->check()) {
            return redirect()->route('register')
                ->with('info', 'Зарегистрируйтесь, чтобы выбрать тариф "' . $plan->name . '"');
        }

        $user = auth()->user();

        // Бесплатный тариф — просто активируем
        if ($plan->price == 0) {
            $user->update([
                'subscription_plan' => $planKey,
                'subscription_ends_at' => now()->addDays($plan->duration_days),
            ]);
            return redirect()->route('chat.show')
                ->with('success', 'Тариф «' . $plan->name . '» активирован!');
        }

        // ЗАЩИТА ОТ ПОВТОРНОЙ ОПЛАТЫ
        if ($user->subscription_ends_at && $user->subscription_ends_at->isFuture()) {
            $currentPlan = $user->subscription_plan ?? 'start';
            $currentOrder = self::PLAN_ORDER[$currentPlan] ?? 0;
            $newOrder = self::PLAN_ORDER[$planKey] ?? 0;

            if ($newOrder <= $currentOrder) {
                $currentPlanModel = Plan::where('key', $currentPlan)->first();
                $currentPlanName = $currentPlanModel ? $currentPlanModel->name : 'Старт';

                return redirect()->route('pricing')
                    ->with('error', 'У вас уже активна подписка на тариф «' . $currentPlanName . '» до ' .
                        $user->subscription_ends_at->format('d.m.Y') . '. ' .
                        ($newOrder === $currentOrder
                            ? 'Дождитесь окончания подписки для продления.'
                            : 'Для смены тарифа дождитесь окончания текущей подписки.'));
            }
        }

        // === ПРОМОКОД ===
        $promoCodeValue = strtoupper(trim($request->input('promo_code', '')));
        $promo = null;
        $finalPrice = $plan->price;
        $originalAmount = $plan->price;

        if ($promoCodeValue) {
            $promo = PromoCode::where('code', $promoCodeValue)->first();
            if ($promo && $promo->isValid(Auth::user())) {
                $finalPrice = $promo->discountedPrice($plan->price);
            } else {
                $promo = null; // неверный промокод игнорируем
            }
        }

        $orderId = 'order_' . Str::random(16) . '_' . time();

        try {
            $service = new TBankPaymentService();
            $result = $service->initiatePayment([
                'amount' => $finalPrice * 100, // в копейках, уже со скидкой
                'order_id' => $orderId,
                'description' => 'Тариф «' . $plan->name . '» на ' . $plan->getPeriodLabel() 
                    . ($promo ? ' (промокод ' . $promo->code . ')' : ''),
                'email' => $user->email,
                'user_id' => $user->id,
            ]);

            Payment::create([
                'user_id' => $user->id,
                'order_id' => $orderId,
                'payment_id' => $result['payment_id'],
                'plan' => $planKey,
                'amount' => $finalPrice,
                'original_amount' => $originalAmount,
                'promo_code' => $promo ? $promo->code : null,
                'status' => 'NEW',
                'response' => $result,
            ]);

            return redirect($result['payment_url']);
        } catch (\Throwable $e) {
            Log::error('Payment init error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Ошибка создания платежа: ' . $e->getMessage());
        }
    }
}
