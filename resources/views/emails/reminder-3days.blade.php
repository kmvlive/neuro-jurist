<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1f2937; max-width: 600px; margin: 0 auto; padding: 20px; background: #f3f4f6;">
    <div style="background: white; border-radius: 12px; padding: 30px;">
        <h1 style="color: #1e40af;">Здравствуйте, {{ $user->name ?? 'коллега' }}!</h1>
        
        <p>Прошло 3 дня с вашей регистрации, и вы ещё не попробовали все возможности Нейро-юриста.</p>

        <p><strong>Что упускают те, кто не подписался:</strong></p>
        <ul>
            <li>♾️ Безлимитные консультации (вместо 20 бесплатных)</li>
            <li>📂 История всех диалогов</li>
            <li>📎 Прикрепление документов (PDF, DOCX)</li>
            <li>🔍 Полнотекстовый поиск по консультациям</li>
        </ul>

        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 20px; border-radius: 8px; margin: 25px 0;">
            <p style="margin: 0 0 10px; font-weight: 600;">⏰ Специальное предложение</p>
            <p style="margin: 0;">Промокод <strong style="background: white; padding: 4px 12px; border-radius: 4px; font-family: monospace;">{{ $promoCode }}</strong> — скидка 20% на подписку!</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('login') }}" style="background: #1e40af; color: white; padding: 14px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block;">Попробовать сейчас →</a>
        </div>
    </div>
</body>
</html>
