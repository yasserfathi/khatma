<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required', // This can be email or name
            'password' => 'required',
        ]);

        $identity = $request->email;
        $field = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $credentials = [
            $field => $identity,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if (!$user->active) {
                return response()->json(['message' => 'الحساب غير نشط'], 401);
            }
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
        }

        return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'كلمة المرور الحالية غير صحيحة'], 422);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['new_password'])
        ]);

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح']);
    }
}
