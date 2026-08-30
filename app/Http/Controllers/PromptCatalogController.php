<?php

namespace App\Http\Controllers;

use App\Models\PromptCategory;
use App\Models\QuickPrompt;

class PromptCatalogController extends Controller
{
    public function index()
    {
        $sections = PromptCategory::whereNull('parent_id')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($s) {
                $directCount = $s->quickPrompts()->where('active', true)->count();
                $childCount = 0;
                foreach ($s->children()->get() as $child) {
                    $childCount += $child->quickPrompts()->where('active', true)->count();
                }
                $s->total_prompts = $directCount + $childCount;
                return $s;
            });

        $totalPrompts = QuickPrompt::where('active', true)->count();

        return view('prompts.index', compact('sections', 'totalPrompts'));
    }

    public function show($slug)
    {
        $section = PromptCategory::whereNull('parent_id')
            ->where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();

        $section->load([
            'children' => fn($q) => $q->where('active', true)->orderBy('sort_order'),
            'children.quickPrompts' => fn($q) => $q->where('active', true)->orderBy('sort_order'),
            'quickPrompts' => fn($q) => $q->where('active', true)->orderBy('sort_order'),
        ]);

        return view('prompts.show', compact('section'));
    }
}