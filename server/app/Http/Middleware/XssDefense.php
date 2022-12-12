<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XssDefense
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
        $filteredParams = [];
        if (!$request->is('api/login')) {
            foreach ($request->all() as $key => $value) {

                if (is_string($value)) {
                    if ($value === 'ascending') {
                        $filteredParams[$key] = 'asc';
                    } elseif ($value === 'descending') {
                        $filteredParams[$key] = 'desc';
                    } else {
                        $filteredParams[$key] = htmlspecialchars($value);
                    }
                } else {
                    $filteredParams[$key] = $value;
                }
            }
            $request->replace($filteredParams);
        }
        return $next($request);
    }
}
