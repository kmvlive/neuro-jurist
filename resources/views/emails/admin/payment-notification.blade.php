<!DOCTYPE html>
<html><body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px;">
<h2 style="color:#16a34a;">💰 Новый платёж</h2>
<table style="width:100%;border-collapse:collapse;margin:20px 0;">
    <tr><td style="padding:8px;border-bottom:1px solid #eee;color:#666;">Клиент</td><td style="padding:8px;border-bottom:1px solid #eee;"><strong>{{ $user->name }}</strong> ({{ $user->email }})</td></tr>
    <tr><td style="padding:8px;border-bottom:1px solid #eee;color:#666;">Тариф</td><td style="padding:8px;border-bottom:1px solid #eee;">{{ $plan ? $plan->name : $payment->plan }}</td></tr>
    <tr><td style="padding:8px;border-bottom:1px solid #eee;color:#666;">Сумма</td><td style="padding:8px;border-bottom:1px solid #eee;font-size:18px;"><strong>{{ number_format($payment->amount, 0, ',', ' ') }} ₽</strong></td></tr>
    @if($payment->promo_code)
    <tr><td style="padding:8px;border-bottom:1px solid #eee;color:#666;">Промокод</td><td style="padding:8px;border-bottom:1px solid #eee;">{{ $payment->promo_code }} (без скидки {{ number_format($payment->original_amount, 0, ',', ' ') }} ₽)</td></tr>
    @endif
    <tr><td style="padding:8px;color:#666;">Дата</td><td style="padding:8px;">{{ now()->format('d.m.Y H:i') }}</td></tr>
</table>
<a href="{{ config('app.url') }}/admin/users/{{ $user->id }}" style="display:inline-block;background:#2563eb;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;">Открыть карточку клиента</a>
<hr style="margin:30px 0;border:none;border-top:1px solid #eee;">
<p style="font-size:12px;color:#999;">Нейро-юрист · автоматическое уведомление об оплате</p>
</body></html>
