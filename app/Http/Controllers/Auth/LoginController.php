<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    use HasRoles;
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = '/LoginController';          //Temporario, Mauricio.  Variable estática
    protected $redirectTo = '/';                          //Temporario, Mauricio
    
        // protected function redirectTo()
        // {

        //     // if($user->role=='super_admin'){
        //     //     return '/path1';
        //     // }elseif($user->role=='brand_manager'){
        //         return '/accidentes/resumen';
        //     //}   

        // }

            
        //Sobreescribo función redirectPath() del trait RedirectPath (ML)
        public function redirectPath()
        {
            //agrego log de login acá
            Log::info("Login999: ".Auth::user()->name);


            if (method_exists($this, 'redirectTo')) {
                return $this->redirectTo();
                dd('existe metodo redirectto');
            }
            //dd('hola');
            //return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
            if(auth()->user()->hasAnyPermission('ver_accidentes'))
                return '/accidentes/listar';

            if(auth()->user()->hasAnyPermission('ver_reclamos'))
                return '/reclamos/listar';

            return '/debuggeando';     //Si no tiene esos permisos, ingresa a pantalla ppal (pongo que onda para debug)
        }



        /**
         * Create a new controller instance.
         *
         * @return void
         */
        public function __construct()
        {
            $this->middleware('guest')->except('logout');
        }
        


}
