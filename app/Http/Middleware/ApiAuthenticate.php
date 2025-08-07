<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class ApiAuthenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request)
    {
        // For API requests, always return null to avoid redirects
        return null;
    }

    /**
     * Handle unauthenticated user for API requests
     */
    protected function unauthenticated($request, array $guards)
    {
        // For API requests, throw an authentication exception
        // which will be converted to a JSON response
        if ($request->expectsJson() || $request->is('api/*')) {
            abort(401, 'Unauthenticated');
        }

        parent::unauthenticated($request, $guards);
    }
}
