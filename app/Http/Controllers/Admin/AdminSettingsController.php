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
        return view('admin.settings', compact('counterCode'));
    }

    public function update(Request $request)
    {
        Setting::set('counter_code', trim($request->input('counter_code', '')));
        return redirect()->route('admin.settings.edit')->with('success', 'Настройки сохранены');
    }
}
