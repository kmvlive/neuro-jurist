<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuickPrompt;
use App\Models\PromptCategory;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdminQuickPromptsController extends Controller
{
    public function index()
    {
        $prompts = QuickPrompt::with('category')->orderBy('sort_order')->get();
        return view('admin.quick-prompts.index', compact('prompts'));
    }

    public function create()
    {
        $categories = PromptCategory::with('parent')->orderByRaw('COALESCE(parent_id, id) ASC, parent_id IS NOT NULL ASC, sort_order ASC')->get();
        return view('admin.quick-prompts.form', ['prompt' => null, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'key' => 'required|string|max:50|unique:quick_prompts,key|regex:/^[a-z0-9_]+$/',
            'icon' => 'required|string|max:10',
            'active' => 'boolean',
            'sort_order' => 'integer',
            'category_id' => 'nullable|exists:prompt_categories,id',
        ]);
        if ($request->category_id === '' || $request->category_id === '0') $data['category_id'] = null;
        QuickPrompt::create($data);
        return redirect()->route('admin.quick-prompts.index')->with('success', 'Промпт создан');
    }

    public function edit(QuickPrompt $quickPrompt)
    {
        $categories = PromptCategory::with('parent')->orderByRaw('COALESCE(parent_id, id) ASC, parent_id IS NOT NULL ASC, sort_order ASC')->get();
        return view('admin.quick-prompts.form', ['prompt' => $quickPrompt, 'categories' => $categories]);
    }

    public function update(Request $request, QuickPrompt $quickPrompt)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'key' => 'required|string|max:50|unique:quick_prompts,key,' . $quickPrompt->id . '|regex:/^[a-z0-9_]+$/',
            'icon' => 'required|string|max:10',
            'active' => 'boolean',
            'sort_order' => 'integer',
            'category_id' => 'nullable|exists:prompt_categories,id',
        ]);
        if ($request->category_id === '' || $request->category_id === '0') $data['category_id'] = null;
        $quickPrompt->update($data);
        return redirect()->route('admin.quick-prompts.index')->with('success', 'Промпт обновлён');
    }

    public function destroy(QuickPrompt $quickPrompt)
    {
        $quickPrompt->delete();
        return redirect()->route('admin.quick-prompts.index')->with('success', 'Промпт удалён');
    }

    public function editAd(QuickPrompt $quickPrompt)
    {
        $ad = $quickPrompt->ad;
        return view('admin.quick-prompts.ad-form', compact('quickPrompt', 'ad'));
    }

    public function updateAd(Request $request, QuickPrompt $quickPrompt)
    {
        $data = $request->validate([
            'content' => 'required|string',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        if ($quickPrompt->ad) {
            $quickPrompt->ad->update($data);
        } else {
            $data['quick_prompt_id'] = $quickPrompt->id;
            Ad::create($data);
        }

        return redirect()->route('admin.quick-prompts.index')->with('success', 'Реклама сохранена');
    }

    public function toggleAllAds(Request $request)
    {
        $action = $request->input('action');
        if ($action === 'disable') {
            Ad::query()->update(['active' => false]);
            $msg = 'Вся реклама отключена';
        } else {
            Ad::query()->update(['active' => true]);
            $msg = 'Вся реклама включена';
        }
        return redirect()->route('admin.quick-prompts.index')->with('success', $msg);
    }

}
