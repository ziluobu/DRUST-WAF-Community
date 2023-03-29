<?php

namespace App\Http\Controllers\Api;

use App\Jobs\MlogicLogJob;
use App\Models\IpBlack;
use App\Models\RulesSys;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LocalApiController
{
    /**
     * 日志入库接口
     * @param Request $request
     * @return bool|string
     */
    public function index(Request $request)
    {
        try {
            if ($request->ip() !== '127.0.0.1') {
                return response('500', 500)
                    ->header('Content-Type', 'text/plain');
            }

            $data = strToUtf8($request->getContent());
            MlogicLogJob::dispatch($data)->onQueue('mlogic');
            return 'success';
        } catch (\Exception $exception) {
            Log::error('log error=>' . $exception->getMessage());
        }
    }

    /**
     * lua脚本触发封禁ip接口
     * @param Request $request
     * @return bool|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function black(Request $request)
    {
        try {
            if ($request->ip() !== '127.0.0.1') {
                return response('500', 500)
                    ->header('Content-Type', 'text/plain');
            }
            $rules     = [
                "ip" => "required|ip",
                "id" => "required",
            ];
            $param     = $request->all();
            $validator = Validator::make($param, $rules);
            if ($validator->passes()) {
                $ip = $request->get('ip');
                $id = $request->get('id');
                if (!in_array($ip, config('api.allow_ip'))) {
                    app('redis')->connection('rule_black')->hmset('rule_black', [$ip => $id]);
                }
                return true;
            }
        } catch (\Exception $exception) {
            Log::error('black error' . $exception->getMessage());
        }
        return true;
    }

    /*array(11) {
                ["agent"]=>
                string(6) "本机"
                ["city"]=>
                string(6) "北京"
                ["country"]=>
                string(6) "中国"
                ["id"]=>
                string(6) "350347"
                ["info"]=>
                string(33) "39.103.216.142:46400 已经连接"
                ["ip"]=>
                string(14) "39.103.216.142"
                ["model"]=>
                string(3) "new"
                ["project"]=>
                string(11) "Redis蜜罐"
                ["region"]=>
                string(6) "北京"
                ["time"]=>
                string(19) "2021-06-30 15:24:50"
                ["type"]=>
                string(5) "REDIS"
                }*/
    public function hfish(Request $request)
    {
        try {
            if ($request->ip() !== config('modsecurity.hfish_ip')) {
                return response('500', 500)
                    ->header('Content-Type', 'text/plain');
            }
            $rules     = [
                "ip" => "required|ip",
            ];
            $param     = $request->all();
            $validator = Validator::make($param, $rules);
            if ($validator->passes()) {
                $ip = $request->get('ip');
                //判断是否是内网ip
                if (!is_local_ip($ip)) {
                    Log::error('hfish4=>success');
                    app('redis')->connection('rule_black')->hmset('rule_black', [$ip => 0]);
                    $LogstashRedis            = app('redis')->connection('logstash_fish');
                    $param['tag_MessageType'] = "dxhoneypot";
                    $LogstashRedis->lpush('logstash_fish', json_encode($param));
                }
            }
        } catch (\Exception $exception) {
            Log::error('hfish-error=>' . $exception->getMessage());
        }
        return true;
    }

    public function rule(Request $request)
    {
        return true;
        $files = getDirContent(public_path('rule'));
        echo "<pre/>";
        $data = [];
        $reg  = '/id:(\d+),/';
        $i    = 0;
        $date = date('Y-m-d H:i:s');
        foreach ($files as $file_name) {
            $file      = file_get_contents(public_path('rule/') . $file_name);
            $file_name = substr($file_name,
                strpos($file_name, '-', strpos($file_name, '-') + 1) + 1
            );
            $file_name = substr($file_name, 0, strpos($file_name, '.'));
            $file      = preg_replace("/^(#.*)/m", '', $file);
            $file      = preg_replace("/^(\n)/m", '', $file);
            $file      = preg_replace("/^(?!(SecRule|\s)).*/m", '', $file);
            $file      = explode('SecRule', $file);
            foreach ($file as $k => $v) {
                if ($k > 0) {
                    $v = 'SecRule' . $v;
                    preg_match($reg, $v, $matches);
                    $id = $matches[1] ?? 0;
                    if (!$id) {
                        $data[array_key_last($data)]['rule_content'] .= $v;
                    } else {
                        $data[$i]['rule_id']      = $id;
                        $data[$i]['rule_content'] = $v;
                        $data[$i]['rule_type']    = $file_name;
                        $data[$i]['created_at']   = $date;
                        $data[$i]['updated_at']   = $date;
                    }
                    $i++;
                }
            }
        }

        RulesSys::insert($data);
    }

    public function IpRepeat()
    {
        return true;
        $list = IpBlack::groupBy('ip')->pluck('ip', 'id')->toArray();
        $ids  = array_keys($list);
        IpBlack::whereNotIn('id', $ids)->delete();
        $ret = DB::unprepared("alter table ms_ip_black add unique INDEX `ip`(`ip`) ;");
        dd($ret);
    }

    public function signTest(Request $request)
    {
        $secretKey = config('api.sign_secret_key', '');

        $signField = 'sign';

        $return = [];
        $data   = $request->except('file');
        // 移除sign字段
        $return['timestamp'] = time();
        $return['initial']   = $data;
        if (isset($data['sign'])) {
            unset($data['sign']);
        }
        $return['filter'] = $data;
        $data             = array_filter($data);
        ksort($data);
        $return['sort'] = $data;

        $sign = '';
        foreach ($data as $k => $v) {
            if ($signField !== $k) {
                $sign .= $k . $v;
            }
        }
        $return['signStr']             = $sign;
        $return['signStrKey']          = $sign . $secretKey;
        $return['urlencodeSignStrKey'] = urlencode($sign . $secretKey);
        $return['sign']                = md5(urlencode($sign . $secretKey));

        if (md5(urlencode($sign . $secretKey)) === $request->get($signField, null)) {
            $return['verifySign'] = 'success';
        } else {
            $return['verifySign'] = 'false';
        }

        return $return;
    }
}
