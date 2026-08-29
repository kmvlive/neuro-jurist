<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterLink;
use Illuminate\Http\Request;

class AdminFooterLinksController extends Controller
{
    public function index()
    {
        $links = FooterLink::orderBy('sort_order')->get();
        return view('admin.footer-links.index', compact('links'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:500'],
        ]);

        FooterLink::create([
            'title' => $request->title,
            'url' => $request->url,
            'is_external' => $request->boolean('is_external'),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => true,
        ]);

        FooterLink::clearCache();

        return redirect()->route('admin.footer-links.index')
            ->with('success', 'Ссылка добавлена');
    }

    public function edit(FooterLink $footerLink)
    {
        return view('admin.footer-links.edit', compact('footerLink'));
    }

    public function update(Request $request, FooterLink $footerLink)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:500'],
        ]);

        $footerLink->update([
            'title' => $request->title,
            'url' => $request->url,
            'is_external' => $request->boolean('is_external'),
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        FooterLink::clearCache();

        return redirect()->route('admin.footer-links.index')
            ->with('success', 'Ссылка обновлена');
    }

    public function destroy(FooterLink $footerLink)
    {
        $footerLink->delete();
        FooterLink::clearCache();

        return redirect()->route('admin.footer-links.index')
            ->with('success', 'Ссылка удалена');
    }
}
