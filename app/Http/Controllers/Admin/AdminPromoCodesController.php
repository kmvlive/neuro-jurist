<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Http\Request;

class AdminPromoCodesController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::with('user')->latest()->get();
        return view('admin.promo-codes.index', compact('promoCodes'));
    }

    public function create()
    {
        return view('admin.promo-codes.form', ['promoCode' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data = $this->resolveUserEmail($data);
        PromoCode::create($data);
        return redirect()->route('admin.promo-codes.index')->with('success', 'Промокод создан.');
    }

    public function edit(PromoCode $promoCode)
    {
        return view('admin.promo-codes.form', ['promoCode' => $promoCode]);
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $data = $this->validateData($request, $promoCode);
        $data = $this->resolveUserEmail($data);
        $promoCode->update($data);
        return redirect()->route('admin.promo-codes.index')->with('success', 'Промокод обновлён.');
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();
        return redirect()->route('admin.promo-codes.index')->with('success', 'Промокод удалён.');
    }

    private function validateData(Request $request, ?PromoCode $promoCode = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:promo_codes,code,' . ($promoCode?->id ?? 'null')],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'active' => ['boolean'],
            'one_per_user' => ['boolean'],
            'new_users_only' => ['boolean'],
            'user_email' => ['nullable', 'email', 'max:255'],
        ]);
    }

    private function resolveUserEmail(array $data): array
    {
        if (!empty($data['user_email'])) {
            $user = User::where('email', $data['user_email'])->first();
            if ($user) {
                $data['user_id'] = $user->id;
            } else {
                $data['user_id'] = null;
            }
        } else {
            $data['user_id'] = null;
        }
        unset($data['user_email']);
        return $data;
    }
}
