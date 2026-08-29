<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px;">
<h2 style="color:#16a34a;">✅ Спасибо за оплату!</h2>
<p>Здравствуйте, {{ $user->name }}!</p>
<p>Ваш платёж на сумму <strong>{{ number_format($payment->amount, 0, ',', ' ') }} ₽</strong> успешно завершён.</p>

<table style="width:100%;border-collapse:collapse;margin:20px 0;background:#f0fdf4;border-radius:8px;">
    <tr><td style="padding:10px;color:#666;">Тариф</td><td style="padding:10px;"><strong>{{ $plan ? $plan->name : $payment->plan }}</strong></td></tr>
    @if($user->subscription_ends_at)
    <tr><td style="padding:10px;color:#666;">Подписка активна до</td><td style="padding:10px;"><strong>{{ $user->subscription_ends_at->format('d.m.Y') }}</strong></td></tr>
    @endif
    @if($payment->promo_code)
    <tr><td style="padding:10px;color:#666;">Промокод</td><td style="padding:10px;">{{ $payment->promo_code }} — скидка применена 🎉</td></tr>
    @endif
</table>

<p>Теперь вам доступны все возможности тарифа: безлимитные консультации, составление документов и приоритетные ответы.</p>

<a href="{{ config('app.url') }}/chat" style="display:inline-block;background:#2563eb;color:#fff;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:bold;">Перейти в чат →</a>

<hr style="margin:30px 0;border:none;border-top:1px solid #eee;">
<p style="font-size:12px;color:#999;">Вопросы? Напишите на support@neiro-jurist.ru — мы всегда на связи.<br>Нейро-юрист · {{ config('app.url') }}</p>
</body></html>
