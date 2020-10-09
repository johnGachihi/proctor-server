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
    public function bootstrap() {
        return response()->json(['user' => Auth::user()]);
    }

    public function logout() {
        Auth::logout();
    }
}
