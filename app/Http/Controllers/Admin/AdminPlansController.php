<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class AdminPlansController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', 'unique:plans,key'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
        ]);

        Plan::create([
            'key' => strtolower($request->key),
            'name' => $request->name,
            'price' => $request->price,
            'old_price' => $request->filled('old_price') ? (int) $request->old_price : null,
            'duration_days' => (int) $request->input('duration_days', 30),
            'period' => $request->price == 0 ? 'бесплатно' : 'мес',
            'currency' => '₽',
            'features' => [],
            'limitations' => [],
            'highlighted' => false,
            'button_text' => $request->price == 0 ? 'Выбрать' : 'Купить',
            'sort_order' => 99,
            'is_active' => true,
        ]);

        Plan::clearCache();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Тариф создан. Теперь заполните его преимущества.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'period' => ['required', 'string', 'max:50'],
            'currency' => ['required', 'string', 'max:10'],
            'button_text' => ['required', 'string', 'max:50'],
        ]);

        $features = array_values(array_filter(array_map('trim', explode("\n", $request->input('features', '')))));
        $limitations = array_values(array_filter(array_map('trim', explode("\n", $request->input('limitations', '')))));

        $plan->update([
            'name' => $request->name,
            'price' => $request->price,
            'old_price' => $request->filled('old_price') ? (int) $request->old_price : null,
            'duration_days' => (int) $request->input('duration_days', 30),
            'period' => $request->period,
            'currency' => $request->currency,
            'features' => $features,
            'limitations' => $limitations,
            'highlighted' => $request->boolean('highlighted'),
            'button_text' => $request->button_text,
            'sort_order' => (int) $request->input('sort_order', $plan->sort_order),
            'is_active' => $request->boolean('is_active'),
        ]);

        Plan::clearCache();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Тариф «' . $plan->name . '» обновлён.');
    }

    public function destroy(Plan $plan)
    {
        $name = $plan->name;
        $plan->delete();
        Plan::clearCache();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Тариф «' . $name . '» удалён.');
    }
}
