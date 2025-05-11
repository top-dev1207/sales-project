<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Menus\GetSidebarMenu;
use App\Models\Menulist;
use App\Models\RoleHierarchy;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Support\Facades\redirect;

class GetMenu
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    use HasRoles;
    public function handle($request, Closure $next)
    {
        //Comento y pruebo middleware acceso a pagina
        //echo "Rio me esta molestando";
        //dd(URL::spy());
        //dd(Redirect::back());
        return $next($request);
        
        // if (Auth::check()){
        //     $role = 'guest';
        //     //$role =  Auth::user()->menuroles;
        //     $userRoles = Auth::user()->getRoleNames();
        //     //dd(auth()->user()->menuroles);
        //     //$userRoles = (auth()->user());
        //     //$userRoles = $userRoles['items'];
        //     //dd($userRoles);
        //     $roleHierarchy = RoleHierarchy::select('role_hierarchy.role_id', 'roles.name')
        //         ->join('roles', 'roles.id', '=', 'role_hierarchy.role_id')
        //         ->orderBy('role_hierarchy.hierarchy', 'asc')->get();
        //     $flag = false;
        //     foreach($roleHierarchy as $roleHier){
        //         foreach($userRoles as $userRole){
        //             if($userRole == $roleHier['name']){
        //                 $role = $userRole;
        //                 $flag = true;
        //                 break;
        //             }
        //         }
        //         if($flag === true){
        //             break;
        //         }
        //     }
        //     //dd($role);
            
        // }
        // else{
        //     $role = 'guest';
        //     //echo($role);//Mauricio
        // }
        // //session(['prime_user_role' => $role]);
        // $menus = new GetSidebarMenu();
        // $menulists = Menulist::all();
        // $result = array();
        // //dd($role);
        // //$role='guest'; //Mauricio
        // foreach($menulists as $menulist){
        //     $result[ $menulist->name ] = $menus->get( $role, $menulist->id );
        // }
        // //dd($result);
        // view()->share('appMenus', $result );
        // return $next($request);
    }
}