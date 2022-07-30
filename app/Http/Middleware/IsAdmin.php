<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class IsAdmin
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
        define('ADMIN_ID_LOCAL', "5695fbbd-4675-4b2a-b31d-603252c21c94");

        $userId = auth()->user()->id;

        $user = User::find($userId);

        $isAdmin = $user->roles->contains(ADMIN_ID_LOCAL);

        if (!$isAdmin) {
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
