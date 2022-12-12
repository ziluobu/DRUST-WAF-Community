<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\Opertlog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OpertlogMiddleware
{
    protected $secretFields = [
        'password',
        'password_confirmation',
    ];

    protected $except = [
        'api/login',
        'api/logout'
    ];


    protected $defaultAllowedMethods = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if ($this->shouldLogOperation($request)) {
            $originalContent = $response->getOriginalContent();
            $request_method  = strtoupper($request->method());

            $modules = Opertlog::$modules;
            $pathArr = explode('/', $request->path());
            if (isset($pathArr[1])) {
                $path                     = $pathArr[1];
                $opertlog                 = new  Opertlog();
                $opertlog->module         = isset($modules[$path]) ? $modules[$path] : '其他';
                $opertlog->request_method = $request_method;
                $admin_id                 = $request->input('user_id') ?? 0;
                $opertlog->admin_id       = $admin_id;
                $opertlog->username       = Admin::getUserNameById($admin_id);
                $opertlog->oper_url       = $request->path();
                $opertlog->oper_ip        = $request->ip();
                $ipInfo                   = getIpInfo($request->ip());
                $opertlog->oper_location  = $ipInfo['country'].$ipInfo['province'].$ipInfo['city'].$ipInfo['county'];
                $opertlog->oper_param     = $this->formatInput($request->except(['loginUsername','user_id','user_group_id']));
                $opertlog->json_result    = json_encode($originalContent, JSON_UNESCAPED_UNICODE);
                $opertlog->status         = $response->status();
                $opertlog->api_status     = $response->status() == 200 ? $originalContent->code : $response->status();
                $opertlog->save();
            }
        }


        return $response;
    }

    /**
     * @param  array  $input
     *
     * @return string
     */
    protected function formatInput(array $input)
    {
        foreach ($this->getSecretFields() as $field) {
            if ($field && !empty($input[$field])) {
                $input[$field] = Str::limit($input[$field], 3, '******');
            }
        }

        return json_encode($input, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param  string  $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    protected function setting($key, $default = null)
    {
        return config('api.operation_log.'.$key, $default);
    }

    /**
     * @param  Request  $request
     *
     * @return bool
     */
    protected function shouldLogOperation(Request $request)
    {
        return config('api.operation_log.enable')
               && !$this->inExceptArray($request)
               && $this->inAllowedMethods($request->method());
    }

    /**
     * Whether requests using this method are allowed to be logged.
     *
     * @param  string  $method
     *
     * @return bool
     */
    protected function inAllowedMethods($method)
    {
        $allowedMethods = collect($this->getAllowedMethods())->filter();

        if ($allowedMethods->isEmpty()) {
            return true;
        }

        return $allowedMethods->map(function ($method) {
            return strtoupper($method);
        })->contains($method);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except() as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }

    protected function except()
    {
        return array_merge((array)$this->setting('except'), $this->except);
    }

    protected function getSecretFields()
    {
        return array_merge((array)$this->setting('secret_fields'), $this->secretFields);
    }

    protected function getAllowedMethods()
    {
        return (array)($this->setting('allowed_methods') ?: $this->defaultAllowedMethods);
    }
}
