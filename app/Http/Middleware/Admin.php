<?php

namespace App\Http\Middleware;

use Closure;

class Admin
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
        //$roles = explode(',', $request->user()->menuroles);
        //if ( ! in_array('admin', $roles) ) {
        //$roles = $request->user()->getRoleNames();
        if($request->user()->hasRole('admin')||$request->user()->hasRole('developer'))
            return $next($request);
        else
            return abort( 401 );        //Mauricio
        
    }
}
