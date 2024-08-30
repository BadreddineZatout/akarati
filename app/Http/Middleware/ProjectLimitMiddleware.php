<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class ProjectLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user= $request->user();
        if ($user && $user->hasAnyRoles(['promoter']) && Route::is('filament.admin.resources.projects.store')){
           if(count($user->promoter_project) <= (int)(setting('project_limit',3))){
               return $next($request);
           }else{
               return redirect()->back()->with('error', 'you reach project limit');
           }
        }
        return $next($request);
    }
}
