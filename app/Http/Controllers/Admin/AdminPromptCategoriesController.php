<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromptCategory;
use Illuminate\Http\Request;

class AdminPromptCategoriesController extends Controller
{
    public function index()
    {
        $categories = PromptCategory::with('parent')
            ->orderByRaw('COALESCE(parent_id, id) ASC, parent_id IS NOT NULL ASC, sort_order ASC')
            ->get();
        
        return view('admin.prompt-categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = PromptCategory::whereNull('parent_id')->orderBy('sort_order')->get();
        return view('admin.prompt-categories.form', ['category' => null, 'parents' => $parents]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'parent_id' => 'nullable|exists:prompt_categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:prompt_categories,slug|regex:/^[a-z0-9_-]+$/',
            'icon' => 'nullable|string|max:10',
            'sort_order' => 'integer',
            'active' => 'in:0,1',
        ]);

        $data['active'] = $request->has('active');
        PromptCategory::create($data);

        return redirect()->route('admin.prompt-categories.index')->with('success', 'Категория создана');
    }

    public function edit(PromptCategory $promptCategory)
    {
        $parents = PromptCategory::whereNull('parent_id')
            ->where('id', '!=', $promptCategory->id)
            ->orderBy('sort_order')
            ->get();
        
        return view('admin.prompt-categories.form', ['category' => $promptCategory, 'parents' => $parents]);
    }

    public function update(Request $request, PromptCategory $promptCategory)
    {
        $data = $request->validate([
            'parent_id' => 'nullable|exists:prompt_categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:prompt_categories,slug,' . $promptCategory->id . '|regex:/^[a-z0-9_-]+$/',
            'icon' => 'nullable|string|max:10',
            'sort_order' => 'integer',
            'active' => 'in:0,1',
        ]);

        $data['active'] = $request->has('active');
        $promptCategory->update($data);

        return redirect()->route('admin.prompt-categories.index')->with('success', 'Категория обновлена');
    }

    public function destroy(PromptCategory $promptCategory)
    {
        $promptCategory->delete();
        return redirect()->route('admin.prompt-categories.index')->with('success', 'Категория удалена');
    }
}
