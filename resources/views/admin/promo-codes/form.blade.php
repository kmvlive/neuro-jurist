@extends('layouts.app')

@section('title', ($promoCode ? 'Редактировать' : 'Создать') . ' промокод — Админ-панель')

@section('content')
<div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('admin.promo-codes.index') }}" class="text-primary hover:underline">← К промокодам</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-4 mb-6">{{ $promoCode ? 'Редактировать промокод' : 'Новый промокод' }}</h1>

    <form method="POST" action="{{ $promoCode ? route('admin.promo-codes.update', $promoCode) : route('admin.promo-codes.store') }}" class="bg-white shadow rounded-lg p-6 space-y-4">
        @csrf
        @if($promoCode) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Код (латиницей, без пробелов)</label>
            <input type="text" name="code" value="{{ old('code', $promoCode?->code) }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2 font-mono uppercase" placeholder="LAW20">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Скидка, %</label>
            <input type="number" name="discount_percent" min="1" max="100" value="{{ old('discount_percent', $promoCode?->discount_percent ?? 10) }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Лимит использований (пусто = без лимита)</label>
            <input type="number" name="max_uses" min="1" value="{{ old('max_uses', $promoCode?->max_uses) }}"
                   class="w-full border border-gray-300 rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Действует до (пусто = бессрочно)</label>
            <input type="date" name="expires_at" value="{{ old('expires_at', $promoCode?->expires_at?->format('Y-m-d')) }}"
                   class="w-full border border-gray-300 rounded px-3 py-2">
        </div>

        <div class="border-t border-gray-200 pt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Ограничения</h3>
            
            <label class="flex items-start gap-2 mb-3">
                <input type="hidden" name="one_per_user" value="0">
                <input type="checkbox" name="one_per_user" value="1" {{ old('one_per_user', $promoCode?->one_per_user ?? false) ? 'checked' : '' }} class="rounded mt-0.5">
                <div>
                    <span class="text-sm font-medium text-gray-700">Один раз на пользователя</span>
                    <p class="text-xs text-gray-500 mt-0.5">Каждый клиент сможет использовать этот промокод только один раз</p>
                </div>
            </label>

            <label class="flex items-start gap-2 mb-3">
                <input type="hidden" name="new_users_only" value="0">
                <input type="checkbox" name="new_users_only" value="1" {{ old('new_users_only', $promoCode?->new_users_only ?? false) ? 'checked' : '' }} class="rounded mt-0.5">
                <div>
                    <span class="text-sm font-medium text-gray-700">Только для новых клиентов</span>
                    <p class="text-xs text-gray-500 mt-0.5">Промокод работает только для тех, у кого нет активных подписок и оплат</p>
                </div>
            </label>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Персональный код для пользователя (email)</label>
                <input type="email" name="user_email" value="{{ old('user_email', $promoCode?->user?->email) }}"
                       class="w-full border border-gray-300 rounded px-3 py-2" placeholder="client@example.com">
                <p class="text-xs text-gray-500 mt-1">Если заполнено — промокод работает только для этого клиента</p>
            </div>
        </div>

        <label class="flex items-center gap-2">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" {{ old('active', $promoCode?->active ?? true) ? 'checked' : '' }} class="rounded">
            <span class="text-sm font-medium text-gray-700">Промокод активен</span>
        </label>

        <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg hover:bg-blue-700 font-medium">Сохранить</button>
    </form>
</div>
@endsection
