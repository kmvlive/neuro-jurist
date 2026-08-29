<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TBankWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();
        Log::info('TBank webhook received', $data);

        $paymentId = $data['PaymentId'] ?? null;
        $status = $data['Status'] ?? 'UNKNOWN';

        if (!$paymentId) {
            return response()->json(['error' => 'No PaymentId'], 400);
        }

        $payment = Payment::where('payment_id', $paymentId)->first();

        if (!$payment) {
            Log::warning('Payment not found', ['payment_id' => $paymentId]);
            return response()->json(['status' => 'OK']);
        }

        // ЗАЩИТА ОТ ПОВТОРНЫХ ВЕБХУКОВ
        if ($payment->tinkoff_status === 'CONFIRMED' && $status !== 'REFUNDED') {
            Log::info('Duplicate webhook ignored - payment already confirmed', [
                'payment_id' => $paymentId,
                'status' => $status,
            ]);
            return response()->json(['status' => 'OK']);
        }

        $isPaid = in_array($status, ['CONFIRMED']);

        $payment->update([
            'status' => $status,
            'tinkoff_status' => $status,
            'response' => $data,
            'paid_at' => $isPaid ? now() : $payment->paid_at,
        ]);

        if ($isPaid) {
            $user = User::find($payment->user_id);
            if ($user) {
                $plan = Plan::where('key', $payment->plan)->first();
                $durationDays = $plan ? $plan->duration_days : 30;

                $currentEnd = $user->subscription_ends_at && $user->subscription_ends_at->isFuture()
                    ? $user->subscription_ends_at
                    : now();

                $newEndAt = $currentEnd->copy()->addDays($durationDays);

                $user->update([
                    'subscription_plan' => $payment->plan,
                    'subscription_ends_at' => $newEndAt,
                    'free_messages_used' => 0,
                ]);

                // Увеличиваем счётчик использований промокода
                if ($payment->promo_code) {
                    PromoCode::where('code', $payment->promo_code)->increment('used_count');
                }

                // Письмо клиенту об успешной оплате
                try {
                    \Illuminate\Support\Facades\Mail::send('emails.client.payment-success', [
                        'user' => $user, 'payment' => $payment, 'plan' => $plan,
                    ], function ($m) use ($user) {
                        $m->to($user->email, $user->name)->subject('✅ Ваша подписка активна!');
                    });
                } catch (\Throwable $e) {
                    Log::error('Client payment email failed: ' . $e->getMessage());
                }

                Log::info('Subscription activated', [
                    'user_id' => $user->id,
                    'plan' => $payment->plan,
                    'duration_days' => $durationDays,
                    'ends_at' => $user->subscription_ends_at,
                    'payment_id' => $paymentId,
                ]);
            }
        }

        if ($status === 'REFUNDED') {
            Log::warning('Payment refunded', [
                'payment_id' => $paymentId,
                'user_id' => $payment->user_id,
            ]);
        }

        return response()->json(['status' => 'OK']);
    }

    public function success()
    {
        return view('pricing.success');
    }

    public function cancel()
    {
        return view('pricing.cancel');
    }
}
