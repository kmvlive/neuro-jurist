<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;

class TrackPageView
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->isMethod('GET') && !$request->is('admin*') && !$request->is('storage*')) {
            PageView::create([
                'path' => '/' . $request->path(),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
            ]);
        }

        return $response;
    }
}
