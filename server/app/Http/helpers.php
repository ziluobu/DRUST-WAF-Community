<?php

/**
 * 处理接口数据为字符串
 */
if (!function_exists('recursive_str')) {
    function recursive_str($data)
    {
        if (!$data) {
            return $data;
        }
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = recursive_str($data[$k]);
            } elseif (!is_string($v)) {
                if (!is_bool($v)) {
                    $data[$k] = (string)$v;
                }
            }
        }
        return $data;
    }
}

/**
 * token 加密
 */
if (!function_exists('token_encode')) {
    function token_encode($user_id)
    {
        $key = config('api.jwt_key');
        $payload = [
            "user_id" => $user_id,
            "ip" => request()->ip(),
            "user-agent" => request()->userAgent(),
            "time" => time(),
        ];
        $token = \Firebase\JWT\JWT::encode($payload, $key, 'HS256');
        $check_ip = getConfig('check_ip');
        $expireTime = getConfig('token_expire_time');
        if ($expireTime <= 0) {
            $expireTime = 3600;
        }
        if ($check_ip) {
            app('redis')->connection('token')->setex($user_id, $expireTime, $token);
        } else {
            app('redis')->connection('token')->setex($token, $expireTime, $user_id);
        }
        return $token;
    }
}

/**
 * token解密
 */
if (!function_exists('token_decode')) {
    function token_decode($token)
    {
        try {
            $key = config('api.jwt_key');
            $data = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
            return (array)$data;
        } catch (\Exception $e) {
            throw new \App\Exceptions\ApiException('登录已失效,请重新登录', 4100);
        }
    }
}

/**
 * 获取IP信息
 * @param $ip
 * @return array|mixed|string[]
 */
function getIpInfo($ip)
{
    return \itbdw\Ip\IpLocation::getLocation($ip);
}

function getgeoIpInfo($ip)
{
    $ip = trim($ip);
    $info = geoip($ip)->toArray();
    if (isset($info['country']) && in_array($info['country'], ['香港', '台湾', '澳门'])) {
        $info['city'] = $info['country'];
        $info['state_name'] = $info['country'];
        $info['country'] = '中国';
    }
    return $info;
}

/**
 * 获取配置
 * @param $key
 * @return array|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
 */
function getConfig($key)
{
    $value = app('redis')->connection('cache')->hget('config', $key);
    if (!isset($value)) {
        $value = config('api.' . $key);
    }
    return $value;
}

/**
 * 获取超级管理员权限
 * @return array
 */
function getAdminPermit()
{
    $routes = app('router')->getRoutes();
    foreach ($routes as $route) {
        if (isset($route->action['middleware']) && in_array('api.super', $route->action['middleware'])) {
            if (isset($route->action['as'])) {
                $data[] = 'api.' . $route->action['as'];
            }
        }
    }
    return $data;
}

/**
 * 同步站点
 * @param $data
 * @return void
 * @throws Exception
 */
function reload_webs($data = [])
{
    if (!$data) {
        $data = \App\Models\Web::whereNotNull('web_content')
            ->get(['web_name', 'web_content', 'web_port']);
    }
    if ($data) {
        $cmd = 'sudo rm -f ' . config('modsecurity.vhost_path') . '*.conf';
        exec_system($cmd);
        foreach ($data as $v) {
            $v = $v->makeVisible('web_content')->toArray();
            if ($v['web_content']) {
                $filename = config('modsecurity.vhost_path') . $v['web_name'] . '-' . $v['web_port'] . '.conf';
                make_file($filename);
                file_put_contents($filename, $v['web_content'], LOCK_EX);
            }
        }
    }
    graceful();
}

function reset_web_rules($rules = [])
{
    if (!$rules) {
        $rules = \App\Models\Rules::whereIn('status', ['1', '2'])
            ->whereNotNull('rule_content')
            ->pluck('rule_content')->toArray();
    }
    $rules = array_filter($rules);
    //     $ip_defense = \Illuminate\Support\Facades\Cache::get('ip_defense', 0);
    //     if ($ip_defense) {
    //         $str     = <<<EOT
    // SecRule REMOTE_ADDR "@geoLookup" "chain,id:22,deny,phase:1,nolog,auditlog,msg:'Non-China IP address'"
    // SecRule GEO:COUNTRY_CODE "!@rx CN|HK|TW|MO
    // EOT;
    //         $rules[] = str_replace("\r\n", "\n", $str);
    //     }
    $rule_file = config('modsecurity.customize_rules');
    make_file($rule_file);
    $file_info = implode(PHP_EOL, $rules);
    $ret = file_put_contents($rule_file, $file_info . PHP_EOL, LOCK_EX);
    if ($ret) {
        graceful();
    } else {
        throw new \Exception('更新规则库失败');
    }
}


function reset_global_rules($rules = [])
{
    if (!$rules) {
        $rules = \App\Models\RulesGlobal::whereIn('status', ['1', '2'])
            ->whereNotNull('rule_content')
            ->pluck('rule_content')->toArray();
    }
    $rules = array_filter($rules);
    $rule_file = config('modsecurity.global_rules');
    make_file($rule_file);
    $file_info = implode(PHP_EOL, $rules);
    $ret = file_put_contents($rule_file, $file_info . PHP_EOL, LOCK_EX);
    if ($ret) {
        graceful();
    } else {
        throw new \Exception('更新规则库失败');
    }
}

function reset_sys_rules($rules = [])
{
    if (!$rules) {
        $rules = \App\Models\RulesSys::where('is_black', 1)
            ->whereNotNull('black_append_rule')
            ->pluck('black_append_rule')->toArray();
    }
    $rules = array_filter($rules);
    $rule_file = config('modsecurity.update_action');
    $file_info = implode(PHP_EOL, $rules);
    $ret = file_put_contents($rule_file, $file_info . PHP_EOL, LOCK_EX);
    if ($ret) {
        graceful();
    } else {
        throw new \Exception('更新规则库失败');
    }
}

function reset_white_sysrules($rules = [])
{
    if (!$rules) {
        $rules = \App\Models\RulesWhite::where('status', 1)
            ->whereNotNull('rule_content')
            ->pluck('rule_content')->toArray();
    }
    $rules = array_filter($rules);
    $rule_file = config('modsecurity.white_rule');
    make_file($rule_file);
    $file_info = implode(PHP_EOL, $rules);
    $ret = file_put_contents($rule_file, $file_info . PHP_EOL, LOCK_EX);
    if ($ret) {
        graceful();
    } else {
        throw new \Exception('更新规则库失败');
    }
}


/**
 * Note    : 查看文件是否存在
 * Time    : 2021-02-03  11:22
 * @param $path
 * @return bool
 * @throws Exception
 */
function make_file($path)
{
    if (!is_file($path)) {
        $cmd = "sudo touch  $path  && sudo chmod 664  $path  2>&1";
        $result = exec_system($cmd);
        if ($result !== 0) {
            throw new \Exception('文件创建失败=>' . $result);
        }
    }
    return true;
}

/**
 * 异步执行系统命令
 * @param $cmd
 * @return int|string
 */
function exec_system($cmd)
{
    $command = new \App\Http\Extensions\ExecAsync($cmd);
    $command->run();
    while ($line = $command->hasFinished()) {
        if ($line === 'waiting') {
            usleep(500000);
        } else {
            flush();
            break;
        }
    }
    if ($line === 'success') {
        return 0;
    }

    return $line;
}

/**
 * 重载apache
 * @return void
 * @throws Exception
 */
function graceful()
{
    $cmd = 'sudo ' . config('modsecurity.graceful');
    $result = exec_system($cmd);
    if ($result !== 0) {
        throw new \Exception('防火墙同步失败=>' . $result);
    }
}

/**
 * 导入黑名单时转换时间
 * @param $time
 * @return float|int
 */
function timetosecond($time)
{
    $type = mb_substr($time, -1);
    $num = mb_substr($time, 0, -1);
    switch ($type) {
        case '天':
            $second = is_numeric($num) ? $num * 86400 : 0;
            break;
        default:
            $second = 0;
    }

    return $second;
}

/**
 * Note    : 更新服务器白名单列表
 * Time    : 2021-02-03  11:20
 * @param array $ips
 * @throws Exception
 */
function reload_allow_ips($ips = [])
{
    if (!$ips) {
        $ips = \App\Models\IpAllow::where('expire_time', '>', time())
            ->pluck('ip')->toArray();
    }
    $ips = implode(PHP_EOL, $ips);

    $path = config('modsecurity.ip_allow');
    make_file($path);
    $ips .= PHP_EOL;
    $ret = file_put_contents($path, $ips, LOCK_EX);
    if ($ret) {
        graceful();
    } else {
        throw new \Exception('同步白名单失败');
    }
}


function transTime($type, $num)
{
    switch ($type) {
        case 1:
            $times = 3600 * $num;
            break;
        case 2:
            $times = 86400 * $num;
            break;
        default:
            $times = 0;
    }
    return $times;
}

function addRedisRuleType($rule_id, $type = 0)
{
    $redis = app('redis')->connection('rules-type');
    $redis->set($rule_id, $type);
}

function addBlackTime($rule_id, $black_time)
{

    $redis = app('redis')->connection('black_time');
    $redis->set($rule_id, $black_time);
}

function delRedisRuleType($rule_id)
{
    $redis = app('redis')->connection('rules-type');
    $redis->del([$rule_id]);
}

function delBlackTime($rule_id)
{
    $redis = app('redis')->connection('black_time');
    $redis->del([$rule_id]);
}


/**
 * 生成网站规则
 * @param $rule
 * @return array|string|string[]
 */
function generaWebRuleContent($rule)
{
    $rule_str = '';
    if ($rule->is_black == 1) {
        $lua_path = base_path('sh/black.lua');
        $rule_str = ",exec:$lua_path";
    }
    $data = [];
    //去除http
    $request_domain = $rule->getWeb->web_name;

    $rule_id = $rule->id + 440000;
    switch ($rule->status) {
        case 1:
            $status = 'deny';
            break;
        case 2:
            $status = 'pass';
            break;
    }
    $data[] = <<<EOT
SecRule SERVER_NAME "@eq $request_domain" "chain,phase:2,$status,msg:$rule->describe,nolog,auditlog,id:$rule_id$rule_str"
EOT;
    if ($rule->request_uri) {
        $rule->request_uri = strtolower($rule->request_uri);
        $data[] = <<<EOT
SecRule REQUEST_FILENAME "@beginsWith $rule->request_uri" "chain,t:lowercase$rule_str"
EOT;
    }
    // $rule->request_method = json_decode($rule->request_method, true);
    $method_count = count($rule->request_method);
    if ($method_count && $method_count < count(\App\Models\Rules::$request_method)) {
        //说明不是全部请求方式
        $request_methods = '';
        foreach ($rule->request_method as $v) {
            $request_methods .= $v . '|';
        }
        $request_methods = rtrim($request_methods, '|');

        $data[] = <<<EOT
SecRule REQUEST_METHOD "^(?:$request_methods)$" "chain,t:none$rule_str"
EOT;
    }
    //请求位置
    $param_content = $rule->param_content;
    if ($rule->param_site > 0 && $param_content) {
        switch ($rule->param_site) {
            case 1:
                foreach ($param_content as $v) {
                    if (!isset($v['operator']) || !isset($v['value'])) {
                        //过滤掉
                        continue;
                    }
                    $value = strtolower($v['value']);
                    $operator = $v['operator'];
                    if (isset($v['key'])) {
                        $key = $v['key'];
                        $data[] = <<<EOT
SecRule ARGS_GET:$key "$operator $value" "chain,t:lowercase$rule_str"
EOT;
                    } else {
                        $data[] = <<<EOT
SecRule QUERY_STRING "$operator $value" "chain,t:lowercase$rule_str"
EOT;
                    }
                }
                break;
            case 2:
                foreach ($param_content as $v) {
                    if (!isset($v['operator']) || !isset($v['value'])) {
                        //过滤掉
                        continue;
                    }
                    $value = strtolower($v['value']);
                    $operator = $v['operator'];
                    if (isset($v['key'])) {
                        $key = $v['key'];
                        $data[] = <<<EOT
SecRule ARGS_POST:$key "$operator $value" "chain,t:lowercase$rule_str"
EOT;
                    } else {
                        //REQUEST_BODY
                        $data[] = <<<EOT
SecRule REQUEST_BODY "$operator $value" "chain,t:lowercase$rule_str"
EOT;
                    }
                }
                break;
            case 3:
                foreach ($param_content as $v) {
                    if (!isset($v['operator']) || !isset($v['value']) || !isset($v['key'])) {
                        //过滤掉
                        continue;
                    }
                    $value = strtolower($v['value']);
                    $operator = $v['operator'];
                    $key = $v['key'];
                    $data[] = <<<EOT
SecRule REQUEST_HEADERS:$key "$operator  $value" "chain,t:lowercase$rule_str"
EOT;
                }
                break;
        }
    }
    //替换参数
    if (count($data) > 1) {
        foreach ($data as $k => $v) {
            if ($k < array_key_last($data)) {
                if ($rule_str) {
                    $v = str_replace([$rule_str], '', $v);
                }
            } else {
                $v = str_replace(['chain,'], '', $v);
            }
            $data[$k] = $v;
        }
    } else {
        $data[0] = str_replace(['chain,'], '', $data[0]);
    }
    $rule_content = str_replace("\r\n", "\n", implode("\n", $data) . "\n");
    return $rule_content;
}

/**
 * 生成全局规则
 * @param $rule
 * @return array|string|string[]
 */
function generaGlobalRuleContent($rule)
{
    $rule_str = '';
    if ($rule->is_black) {
        $lua_path = base_path('sh/black.lua');
        $rule_str = ",exec:$lua_path";
    }
    $data = [];

    $rule_id = $rule->id + 450000;
    switch ($rule->status) {
        case 1:
            $status = 'deny';
            break;
        case 2:
            $status = 'pass';
            break;
    }
    if ($rule->request_uri) {
        $rule->request_uri = strtolower($rule->request_uri);
        $data[] = <<<EOT
SecRule REQUEST_FILENAME "@beginsWith $rule->request_uri" "chain,msg:$rule->describe,phase:2,$status,nolog,auditlog,id:$rule_id,t:lowercase$rule_str"
EOT;
    }
    // $rule->request_method = json_decode($rule->request_method, true);
    $method_count = count($rule->request_method);
    if ($method_count && $method_count < count(\App\Models\RulesGlobal::$request_method)) {
        //说明不是全部请求方式
        $request_methods = '';
        foreach ($rule->request_method as $v) {
            $request_methods .= $v . '|';
        }
        $request_methods = rtrim($request_methods, '|');

        $data[] = <<<EOT
SecRule REQUEST_METHOD "^(?:$request_methods)$" "chain,msg:$rule->describe,phase:2,$status,nolog,auditlog,id:$rule_id,t:none$rule_str"
EOT;
    }
    //请求位置
    $param_content = $rule->param_content;
    if ($rule->param_site > 0 && $param_content) {
        //        $Operators = \App\Models\RulesGlobal::$Operators;
        switch ($rule->param_site) {
            case 1:
                foreach ($param_content as $v) {
                    if (!isset($v['operator']) || !isset($v['value'])) {
                        //过滤掉
                        continue;
                    }
                    $value = strtolower($v['value']);
                    $operator = $v['operator'];
                    if (isset($v['key'])) {
                        $key = $v['key'];
                        $data[] = <<<EOT
SecRule ARGS_GET:$key "$operator $value" "msg:$rule->describe,phase:2,$status,nolog,auditlog,id:$rule_id,chain,t:lowercase$rule_str"
EOT;
                    } else {
                        //QUERY_STRING
                        $data[] = <<<EOT
SecRule QUERY_STRING "$operator $value" "msg:$rule->describe,phase:2,$status,nolog,auditlog,id:$rule_id,chain,t:lowercase$rule_str"
EOT;
                    }
                }
                break;
            case 2:
                foreach ($param_content as $v) {
                    if (!isset($v['operator']) || !isset($v['value'])) {
                        //过滤掉
                        continue;
                    }
                    $value = strtolower($v['value']);
                    $operator = $v['operator'];
                    if (isset($v['key'])) {
                        $key = $v['key'];
                        $data[] = <<<EOT
SecRule ARGS_POST:$key "$operator $value" "msg:$rule->describe,phase:2,$status,nolog,auditlog,id:$rule_id,chain,t:lowercase$rule_str"
EOT;
                    } else {
                        //REQUEST_BODY
                        $data[] = <<<EOT
SecRule REQUEST_BODY "$operator $value" "msg:$rule->describe,phase:2,$status,nolog,auditlog,id:$rule_id,chain,t:lowercase$rule_str"
EOT;
                    }
                }
                break;
            case 3:
                foreach ($param_content as $v) {
                    if (!isset($v['operator']) || !isset($v['value']) || !isset($v['key'])) {
                        //过滤掉
                        continue;
                    }
                    $value = strtolower($v['value']);
                    $operator = $v['operator'];
                    $key = $v['key'];
                    $data[] = <<<EOT
SecRule REQUEST_HEADERS:$key "$operator  $value" "msg:$rule->describe,phase:2,$status,nolog,auditlog,id:$rule_id,chain,t:lowercase$rule_str"
EOT;
                }
                break;
        }
    }

    if (count($data) > 1) {
        foreach ($data as $k => $v) {
            if ($k > array_key_first($data)) {
                $v = str_replace(["msg:$rule->describe,phase:2,$status,nolog,auditlog,id:$rule_id,"], '', $v);
            }
            if ($k < array_key_last($data)) {
                if ($rule_str) {
                    $v = str_replace([$rule_str], '', $v);
                }
            } else {
                $v = str_replace(['chain,'], '', $v);
            }
            $data[$k] = $v;
        }
    } else {
        $data[0] = str_replace(['chain,'], '', $data[0]);
    }
    $rule_content = str_replace("\r\n", "\n", implode("\n", $data) . "\n");
    return $rule_content;
}

/**
 * 系统规则放过
 * @param $rule
 * @return array|string|string[]
 */
function generaWhiteRuleContent($rule)
{
    $data = [];
    //去除http
    $request_domain = $rule->getWeb->web_name;

    $rule_id = $rule->id + 460000;
    if ($rule->remove_sysrule_id == 0) {
        $ctl = "ruleEngine=Off";
    } else {
        $ctl = "ruleRemoveById=$rule->remove_sysrule_id";
    }
    if ($request_domain && $rule->request_uri) {
        $data[] = <<<EOT
SecRule SERVER_NAME "@eq $request_domain" "chain,phase:1,pass,nolog,noauditlog,id:$rule_id,ctl:$ctl"
EOT;
        $data[] = <<<EOT
SecRule REQUEST_FILENAME "@beginsWith $rule->request_uri" "chain,t:lowercase"
EOT;
    } elseif ($request_domain) {
        $data[] = <<<EOT
SecRule SERVER_NAME "@eq $request_domain" "chain,phase:1,pass,nolog,noauditlog,id:$rule_id,ctl:$ctl"
EOT;
    } else {
        $data[] = <<<EOT
SecRule REQUEST_FILENAME "@beginsWith $rule->request_uri" "chain,phase:1,pass,nolog,noauditlog,id:$rule_id,ctl:$ctl"
EOT;
    }

    $method_count = count($rule->request_method);
    if ($method_count && $method_count < count(\App\Models\Rules::$request_method)) {
        //说明不是全部请求方式
        $request_methods = '';
        foreach ($rule->request_method as $v) {
            $request_methods .= $v . '|';
        }
        $request_methods = rtrim($request_methods, '|');

        $data[] = <<<EOT
SecRule REQUEST_METHOD "^(?:$request_methods)$" "t:none"
EOT;
    }

    //替换参数
    if (count($data) > 1) {
        foreach ($data as $k => $v) {
            if ($k = array_key_last($data)) {
                $v = str_replace(['chain,'], '', $v);
            }
            $data[$k] = $v;
        }
    } else {
        $data[0] = str_replace(['chain,'], '', $data[0]);
    }
    $rule_content = str_replace("\r\n", "\n", implode("\n", $data) . "\n");
    return $rule_content;
}

/**
 * 格式转换为utf-8
 * @param $str
 * @return array|false|mixed|string|string[]|null
 */
function strToUtf8($str)
{
    $encode = mb_detect_encoding($str/*, array('UTF-8', "ASCII", "GB2312", "GBK", 'BIG5')*/);
    if ($encode == 'UTF-8') {
        return $str;
    } elseif ($encode === false) {
        return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
        // return iconv("UTF-8", "UTF-8//IGNORE", $str);
        // throw new \Exception('encode false');
    } else {
        return mb_convert_encoding($str, 'UTF-8', $encode);
    }
}

/**
 * Note    : 判断是内网还是外网ip
 * Time    : 2021-02-03  11:23
 * @param $ip
 * @return bool
 */
function is_local_ip($ip)
{
    $ret = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    if ($ret) {
        return false;
    }
    return true;
}


function formatBytes($bytes, $precision = 2)
{
    $units = array("B", "KB", "MB", "GB", "TB");

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    if ($pow > 0 && $pow < 4 && $bytes >= 1000) {

        $bytes /= 1024;
        $pow += 1;
    }
    return sprintf("%.{$precision}f", $bytes) . " " . $units[$pow];
}

function formatDes($str)
{
    return str_replace(["'", '"', ","], '', $str);
}

/**
 * AES-256-CBC 加密
 * @param $data
 * @return mixed|string
 */
function encrypt_cbc($data)
{
    $iv = env("AES_IV");
    $key = env("AES_KEY");
    $text = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($text);
}

/**
 * AES-256-CBC 解密
 * @param $text
 * @return string
 */
function decrypt_cbc($text)
{
    $iv = env("AES_IV");
    $key = env("AES_KEY");
    $decodeText = base64_decode($text);
    $data = openssl_decrypt($decodeText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return json_decode($data,true);
}
