<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class AdminMiddleware
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
        $user = User::all()->count();
        //$role = Role::create(['guard_name' => 'admin', 'name' => 'superadmin']);
        //$permission = Permission::create(['guard_name' => 'admin', 'name' => 'create invoices']);
        
        if (!($user == 1)) {
            if (!Auth::user()->hasPermissionTo('Administer roles & permissions','admin')) //If user does //not have this permission
            {
                abort('403');
            }
        }

        return $next($request);
    }
}