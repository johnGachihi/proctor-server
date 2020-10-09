<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function currentUser() {
        return Auth::user();
    }

    /* Should this function be in this class */
    public function bootstrap(Request $request) {
        return response()->json(['user' => $request->user()]);
    }

    public function logout() {
        Auth::logout();
    }
}
