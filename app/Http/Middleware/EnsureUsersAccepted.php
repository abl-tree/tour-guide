<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Validator;
use Illuminate\Support\Facades\Redirect;

class EnsureUsersAccepted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( $request->user() && $request->user()->accepted_at ) {
            return $next($request);
        }

        Auth::logout();

        return Redirect::back()
                        ->withErrors(['unauthorized' => 'Your account is not authorized.'])
                        ->withInput();
    }
}
