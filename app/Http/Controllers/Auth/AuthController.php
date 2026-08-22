<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Показать форму регистрации
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Регистрация пользователя
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Получаем guest_id из cookie для переноса счётчика сообщений
        $guestId = $request->cookie('nj_guest_id');
        $guestMessagesUsed = session('guest_messages_used', 0);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client',
            'free_messages_used' => $guestMessagesUsed,
        ]);

        Auth::login($user);

        // Привязываем чаты гостя к новому пользователю
        if ($guestId) {
            \App\Http\Controllers\Chat\ChatController::attachGuestChatsToUser($user, $guestId);
        }

        // Если лимит исчерпан, редирект на страницу тарифов
        if ($user->free_messages_used >= 20 && !$user->hasActiveSubscription()) {
            return redirect()->route('pricing')
                ->with('info', 'Бесплатный лимит исчерпан. Выберите тариф, чтобы продолжить.');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Показать форму входа
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Вход пользователя
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Неверный email или пароль.',
        ])->onlyInput('email');
    }

    /**
     * Выход пользователя
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Показать форму восстановления пароля
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Отправка ссылки для сброса пароля
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // Здесь будет логика отправки email через Laravel Notification
        // Для примера просто возвращаем сообщение
        
        return back()->with('status', 'Ссылка для сброса пароля отправлена на ваш email.');
    }

    /**
     * Показать форму сброса пароля
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    /**
     * Сброс пароля
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Здесь будет логика сброса пароля через PasswordBroker
        
        return redirect()->route('login')
            ->with('status', 'Пароль успешно изменен. Теперь вы можете войти.');
    }
}
