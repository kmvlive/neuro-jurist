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
        return view('admin.settings', compact('counterCode', 'aiModel'));
    }

    public function update(Request $request)
    {
        Setting::set('counter_code', trim($request->input('counter_code', '')));
        Setting::set('ai_model', trim($request->input('ai_model', 'deepseek/deepseek-v4-flash')));
        return redirect()->route('admin.settings.edit')->with('success', 'Настройки сохранены');
    }
}
