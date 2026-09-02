<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function edit()
    {
        $counterCode = Setting::get('counter_code');
        $aiModel = Setting::get('ai_model', 'deepseek/deepseek-v4-flash');
        $googleVerification = Setting::get('google_verification');
        $yandexVerification = Setting::get('yandex_verification');
        return view('admin.settings', compact('counterCode', 'aiModel', 'googleVerification', 'yandexVerification'));
    }

    public function update(Request $request)
    {
        Setting::set('counter_code', trim($request->input('counter_code', '')));
        Setting::set('ai_model', trim($request->input('ai_model', 'deepseek/deepseek-v4-flash')));
        Setting::set('google_verification', $this->extractVerification($request->input('google_verification', '')));
        Setting::set('yandex_verification', $this->extractVerification($request->input('yandex_verification', '')));
        return redirect()->route('admin.settings.edit')->with('success', 'Настройки сохранены');
    }

    /**
     * Извлекает код верификации из любого формата:
     * - <meta name="..." content="ABC123">
     * - content="ABC123"
     * - ABC123
     */
    private function extractVerification(?string $raw): string
    {
        $raw = trim((string) $raw);
        if ($raw === '') return '';

        // Если есть content="..." — извлекаем значение
        if (preg_match('/content=["\']([^"\']+)["\']/', $raw, $m)) {
            return $m[1];
        }

        return $raw;
    }
}
