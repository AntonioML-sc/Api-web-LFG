<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        define('SUPER_ADMIN_ID_LOCAL', "a3c06730-7018-467d-8187-cef95f37224d");

        $userId = auth()->user()->id;

        $user = User::find($userId);

        $isSuperAdmin = $user->roles->contains(SUPER_ADMIN_ID_LOCAL);

        if (!$isSuperAdmin) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'route not found'
                ],
                404
            );
        }

        return $next($request);
    }
}
