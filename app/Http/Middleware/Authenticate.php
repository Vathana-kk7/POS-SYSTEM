<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if ($request->expectsJson() || $request->is('api/*') || $request->is('api') || ($request->header('accept') && str_contains($request->header('accept'), 'application/json'))) {
            return null;
        }

        if (route()->has('login')) {
            return route('login');
        }

        return null;
    }
}
