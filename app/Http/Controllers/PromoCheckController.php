<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromoCheckController extends Controller
{
    public function check(Request $request)
    {
        $code = strtoupper(trim($request->input('code', '')));

        if (!$code) {
            return response()->json(['valid' => false, 'message' => 'Введите промокод']);
        }

        $promo = PromoCode::where('code', $code)->first();

        if (!$promo) {
            return response()->json(['valid' => false, 'message' => 'Промокод не найден']);
        }

        $user = Auth::user();

        if (!$promo->isValid($user)) {
            return response()->json([
                'valid' => false,
                'message' => $promo->getValidationMessage($user),
            ]);
        }

        return response()->json([
            'valid' => true,
            'discount' => $promo->discount_percent,
            'message' => 'Промокод применён: скидка ' . $promo->discount_percent . '%',
        ]);
    }
}
