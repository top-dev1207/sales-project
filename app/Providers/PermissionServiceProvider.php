<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        Permission::addGlobalScope('softDeletes', function ($query) {
            $query->whereNull('deleted_at');
        });

        Role::addGlobalScope('softDeletes', function ($query) {
            $query->whereNull('deleted_at');
        });

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    //public function boot()
    //{
        //
    //}
}
