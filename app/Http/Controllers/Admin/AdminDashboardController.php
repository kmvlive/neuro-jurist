<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
