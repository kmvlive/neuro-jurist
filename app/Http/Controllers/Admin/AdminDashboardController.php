<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MessageFeedback;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Админ-панель - главная
     */
    public function index()
    {
        $stats = [
            'totalUsers' => User::count(),
            'totalClients' => User::where('role', 'client')->count(),
            'totalAdmins' => User::where('role', 'admin')->count(),
            'feedbackUp' => MessageFeedback::where('vote', 1)->count(),
            'feedbackDown' => MessageFeedback::where('vote', -1)->count(),
            'feedbackComments' => MessageFeedback::whereNotNull('comment')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
