<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;

class PermissionCheck
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
        $id = $request->input('user_id');
        if ($id > 1) {
            $urlList = Menu::getPermit($id);
            $urlList = array_filter($urlList);
            // 当前访问的路由
            $currentRoute = strtolower($request->route()->getName());
            if (in_array($currentRoute, $urlList)) {
                return $next($request);
            }
            throw new ApiException('权限不足', 4300);
        }
        return $next($request);
    }
}
