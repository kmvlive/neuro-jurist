<?php

namespace App\Http\Controllers;

use App\Models\PromptCategory;
use App\Models\QuickPrompt;

class PromptCatalogController extends Controller
{
    public function index()
    {
        $sections = PromptCategory::sections()
            ->load(['children.quickPrompts' => fn($q) => $q->where('active', true)->orderBy('sort_order')])
            ->load(['quickPrompts' => fn($q) => $q->where('active', true)->orderBy('sort_order')]);

        $totalPrompts = QuickPrompt::where('active', true)->count();

        return view('prompts.index', compact('sections', 'totalPrompts'));
    }
}
