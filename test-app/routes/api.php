<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('auth-profile.token')->get('/me', function (Request $request) {
    return response()->json([
        'id' => $request->user()?->getAuthIdentifier(),
        'auth_id' => Auth::id(),
    ]);
});
