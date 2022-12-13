<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;

class AdminCheck
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $admin_id = $request->input('user_id');
        if ($admin_id > 1) {
            $id = $request->input('user_group_id');
            if ($id > 0) {
                throw new ApiException('权限不足', 4300);
            }
        }

        return $next($request);
    }
}
