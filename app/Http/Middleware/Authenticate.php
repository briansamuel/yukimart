<?php

namespace App\Http\Middleware;


use App\Services\Auth\AuthService;
use App\Services\Auth\AuthPermissionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Auth\Guard;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        
        Log::info('Log request', ['uri' => $request->path(), 'params' => $request->all()]);
        if (!AuthService::checkLogin()) {
            return redirect('login');
        }

        //check permission
        $AuthPermission = new AuthPermissionService($request);
        if (!$AuthPermission->check()) {
            return redirect('denied-permission');
        }

        return $next($request);
    }
}
