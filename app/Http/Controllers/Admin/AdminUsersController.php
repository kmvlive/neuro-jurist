<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUsersController extends Controller
{
    /**
     * Список пользователей
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Поиск по имени или email
        if ($q = $request->input('q')) {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        // Фильтр по тарифу
        if ($plan = $request->input('plan')) {
            $query->where('subscription_plan', $plan);
        }

        // Фильтр по статусу подписки
        if ($subscription = $request->input('subscription')) {
            if ($subscription === 'active') {
                $query->where('subscription_ends_at', '>', now());
            } elseif ($subscription === 'expired') {
                $query->whereNotNull('subscription_ends_at')
                    ->where('subscription_ends_at', '<=', now());
            } elseif ($subscription === 'none') {
                $query->whereNull('subscription_ends_at');
            }
        }

        // Фильтр по роли
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->latest()->paginate(20)->appends($request->query());

        return view('admin.users.index', compact('users'));
    }

    /**
     * Создание пользователя
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Сохранение пользователя
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:client,admin'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'unlimited_messages' => $request->boolean('unlimited_messages'),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно создан.');
    }


    /**
     * Просмотр пользователя
     */
    public function show(User $user)
    {
        $chats = $user->chats()->latest()->limit(10)->get();
        $payments = $user->payments()->latest()->limit(10)->get();
        $totalMessages = \App\Models\Message::whereHas('chat', fn($q) => $q->where('user_id', $user->id))->count();

        return view('admin.users.show', compact('user', 'chats', 'payments', 'totalMessages'));
    }

    /**
     * Редактирование пользователя
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Обновление пользователя
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:client,admin'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'unlimited_messages' => $request->boolean('unlimited_messages'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно обновлен.');
    }

    /**
     * Удаление пользователя
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Нельзя удалить самого себя.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно удален.');
    }
}
