<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; max-width: 600px; margin: 0 auto; padding: 20px; background: #f3f4f6;">
    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #1e40af; font-size: 28px; margin: 0;">⚖️ Нейро-юрист</h1>
            <p style="color: #6b7280; margin: 5px 0 0;">AI-ассистент для юридических задач</p>
        </div>

        <h2 style="color: #1e40af;">Здравствуйте, {{ $user->name ?? 'коллега' }}!</h2>
        
        <p>Спасибо за регистрацию! Теперь у вас есть персональный AI-юрист, который готов отвечать на ваши вопросы 24/7.</p>

        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b; padding: 20px; border-radius: 8px; margin: 25px 0;">
            <p style="margin: 0 0 10px; font-weight: 600;">🎁 Подарок для вас</p>
            <p style="margin: 0;">Используйте промокод <strong style="background: white; padding: 4px 12px; border-radius: 4px; font-family: monospace;">{{ $promoCode }}</strong> и получите скидку 15% на первый месяц подписки!</p>
        </div>

        <p><strong>Что вы можете сделать прямо сейчас:</strong></p>
        <ul style="color: #4b5563;">
            <li>📄 Составить претензию в магазин за 2 минуты</li>
            <li>⚖️ Узнать свои права при сокращении</li>
            <li>🏠 Проверить договор аренды на риски</li>
            <li>💰 Рассчитать компенсации за задержку зарплаты</li>
        </ul>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('login') }}" style="background: #1e40af; color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block;">Начать консультацию →</a>
        </div>

        <p style="color: #6b7280; font-size: 14px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            Если у вас есть вопросы — просто ответьте на это письмо. Мы читаем каждое сообщение.
        </p>
    </div>
</body>
</html>
