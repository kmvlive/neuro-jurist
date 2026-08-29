<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; max-width: 600px; margin: 0 auto; padding: 20px; background: #f3f4f6;">
    <div style="background: white; border-radius: 12px; padding: 30px;">
        <h1 style="color: #dc2626;">Последнее предложение!</h1>
        
        <p>Здравствуйте, {{ $user->name ?? 'коллега' }}!</p>

        <p>Прошла неделя с регистрации, и это наше <strong>последнее письмо</strong> со скидкой.</p>

        <div style="background: #fef2f2; border-left: 4px solid #dc2626; padding: 20px; border-radius: 8px; margin: 25px 0;">
            <p style="margin: 0 0 10px; font-weight: 600;">💸 Максимальная скидка 30%</p>
            <p style="margin: 0;">Промокод <strong style="background: white; padding: 4px 12px; border-radius: 4px; font-family: monospace;">{{ $promoCode }}</strong> действует только 48 часов!</p>
        </div>

        <p><strong>Вам подойдёт подписка, если:</strong></p>
        <ul>
            <li>У вас срочный юридический вопрос</li>
            <li>Нужно составить документы быстро</li>
            <li>Вы хотите разобраться в своих правах</li>
            <li>Часто обращаетесь к юристу за советом</li>
        </ul>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('pricing') }}" style="background: #dc2626; color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block;">Активировать скидку →</a>
        </div>
    </div>
</body>
</html>
