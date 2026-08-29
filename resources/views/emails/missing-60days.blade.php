<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; max-width: 600px; margin: 0 auto; padding: 20px; background: #f3f4f6;">
    <div style="background: white; border-radius: 12px; padding: 30px;">
        <h1 style="color: #6b7280;">Мы вас не видим уже 2 месяца</h1>
        
        <p>Здравствуйте, {{ $user->name ?? 'коллега' }}!</p>

        <p>Вы зарегистрировались в Нейро-юристе, но с тех пор ни разу не заходили. Возможно:</p>
        <ul>
            <li>Не нашлось времени разобраться</li>
            <li>Юридический вопрос решился сам</li>
            <li>Сервис оказался не тем, что вы ожидали</li>
        </ul>

        <p>Если у вас остались вопросы — мы всё ещё здесь. Бесплатные 20 сообщений ждут вас.</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('login') }}" style="background: #1e40af; color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block;">Проверить →</a>
        </div>

        <p style="color: #6b7280; font-size: 14px;">Если вы не хотите получать наши письма, просто напишите в ответ «отписаться».</p>
    </div>
</body>
</html>
