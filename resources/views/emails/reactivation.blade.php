<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; max-width: 600px; margin: 0 auto; padding: 20px; background: #f3f4f6;">
    <div style="background: white; border-radius: 12px; padding: 30px;">
        <h1 style="color: #7c3aed;">Мы по вам соскучились!</h1>
        
        <p>Здравствуйте, {{ $user->name ?? 'коллега' }}!</p>

        <p>Заметили, что вы давно не пользовались Нейро-юристом. Возможно, у вас был плохой опыт или не нашли то, что нужно?</p>

        <p>За последние месяцы мы сделали много улучшений:</p>
        <ul>
            <li>✨ Полностью переработали AI — теперь отвечает ещё точнее</li>
            <li>🔍 Добавили поиск по всем консультациям</li>
            <li>📄 Шаблоны претензий, исков, жалоб</li>
            <li>🎤 Голосовое озвучивание ответов</li>
        </ul>

        <div style="background: #f5f3ff; border-left: 4px solid #7c3aed; padding: 20px; border-radius: 8px; margin: 25px 0;">
            <p style="margin: 0 0 10px; font-weight: 600;">💜 Подарок на возвращение</p>
            <p style="margin: 0;">Промокод <strong style="background: white; padding: 4px 12px; border-radius: 4px; font-family: monospace;">{{ $promoCode }}</strong> — скидка 25% на подписку!</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('login') }}" style="background: #7c3aed; color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block;">Вернуться →</a>
        </div>
    </div>
</body>
</html>
