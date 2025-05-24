<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\UserLoginSession; // Assuming you have a model for user login sessions

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {

        $request->authenticate();

        $request->session()->regenerate();
        //if(!(Auth::user()->can('desarrollar')))     //solo usuarios que no desarrollen

        Log::info("Login: " . Auth::user()->name);
        if (auth()->user()->hasRole('Cajeros')) {
            //dump("ROL CAJERO");
            //return redirect()->route('inicio.seleccionar.caja');

            //return view('crm.homepage');
            //session(['Caja' => 'Arena1']);
            //dump(session('Caja'));
            //dd(session());
            return redirect()->route('seleccionar.caja');

        }
        $userId = Auth::id();

        // Record login time
        UserLoginSession::create([
            'user_id' => $userId,
            'login_time' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->intended(RouteServiceProvider::HOME);

    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $userId = Auth::id();
        // Update the user's latest session record with logout time
        $session = UserLoginSession::where('user_id', $userId)
            ->whereNull('logout_time')
            ->latest('login_time')
            ->first();

        if ($session) {
            $session->update([
                'logout_time' => now(),
                'session_duration' => now()->diffInSeconds(Carbon::parse($session->login_time))
            ]);
        }
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();


        return redirect('/');
    }
}