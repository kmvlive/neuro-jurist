<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageFeedback;
use Illuminate\Http\Request;

class AdminFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $query = MessageFeedback::with(['message.chat', 'user']);

        if ($filter === 'up') {
            $query->where('vote', 1);
        } elseif ($filter === 'down') {
            $query->where('vote', -1);
        }

        $feedbacks = $query->latest()->paginate(30)->withQueryString();

        $stats = [
            'total' => MessageFeedback::count(),
            'up' => MessageFeedback::where('vote', 1)->count(),
            'down' => MessageFeedback::where('vote', -1)->count(),
            'with_comments' => MessageFeedback::whereNotNull('comment')->count(),
        ];

        return view('admin.feedback', compact('feedbacks', 'stats', 'filter'));
    }
}
