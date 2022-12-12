<?php

namespace App\Console\Commands;

use App\Events\SynIptablesActionEvent;
use App\Models\IpBlack;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RuleBlackCommond extends Command
{

    /*
     * 触发规则封禁
     * */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:rule-black';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'rule-black';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 处理触发规则加入的黑名单
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('处理触发规则加入的黑名单IP');
        try {
            $redis = app('redis')->connection('rule_black');
            $list  = $redis->hgetall('rule_black');
            $keys  = array_keys($list);
            if ($list) {
                $data = [];
                //获取所有加入黑名单的id和对应的时间
                $blackTimeRedis = app('redis')->connection('black_time');
                $black_times    = $blackTimeRedis->mget(array_values($list));
                $i              = 0;
                $date           = date('Y-m-d H:i:s');
                foreach ($list as $ip => $id) {
                    $black_type  = 0;
                    $expire_time = 0;
                    if ($id) {
                        if (!is_bool($black_times[$i])) {
                            if ($black_times[$i] > 0) {
                                $black_type  = 1;
                                $expire_time = time() + $black_times[$i];
                            }
                        }
                    }
                    $data[$i]['ip']          = $ip;
                    $data[$i]['admin_id']    = 0;
                    $data[$i]['black_type']  = $black_type;
                    $data[$i]['type']        = $id ? 2 : 3;
                    $data[$i]['rule_id']     = $id;
                    $data[$i]['expire_time'] = $expire_time;
                    $data[$i]['created_at']  = $date;
                    $data[$i]['reason']      = $id ? '触发规则' : '触发蜜罐';
                    $i++;
                }
                IpBlack::insertOrIgnore($data);
                event(new SynIptablesActionEvent(0, $list));
                $redis->hdel('rule_black', $keys);
                $this->info('成功加入黑名单');
            }
            $this->info('检测成功');
        } catch (\Exception $exception) {
            $this->error('处理触发规则加入的黑名单IP失败' . $exception->getMessage());
            // echo 'command:rule-black error' . $exception->getMessage();
            Log::error('command:IpBlack-delete error' . $exception->getMessage());
        }
    }

}
