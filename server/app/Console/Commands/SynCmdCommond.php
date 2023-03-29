<?php

namespace App\Console\Commands;

use App\Models\FailedSyncmd;
use Illuminate\Console\Command;
use Predis\Connection\ConnectionException;

class SynCmdCommond extends Command
{

    /*
     * 同步负载均衡服务器
     * */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncmd {sleep=3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'tongbu shuju';


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
     * type 事件类型
     * 0 同步黑名单
     * 1 同步白名单
     * 2 同步站点
     * 3 同步网站规则
     * 4 同步国外IP防御规则
     * 5 同步全局规则
     * 6 同步系统规则
     * 7 同步自定义系统规则
     */

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //从属服务器运行，同步主服务器防火墙，站点，规则等配置
        //避免主服务器重复运行
        if (!env('IS_MASTER', true)) {
            $this->info('开始同步命令' . date('Y-m-d H:i:s'));
            // echo "syncmd start" . PHP_EOL;
            set_time_limit(0);
            $sleep       = $this->argument('sleep');
            $SynCmdRedis = app('redis')->connection('syncmd');
            /**
             * 循环执行消费
             */
            while (true) {
                try {
                    $data = $SynCmdRedis->rpop(config('modsecurity.syn_list_key'));
                    if ($data) {
                        // echo $data . PHP_EOL;
                        $this->info($data);
                        $data = json_decode($data, true);
                        // TODO 发送消息业务
                        $type = $data['type'];
                        switch ($type) {
                            case 0:
                                \Artisan::call('command:iptables-black-reload');
                                break;
                            case 1:
                                \Artisan::call('command:iptables-allow-reload');
                                reload_allow_ips();
                                break;
                            case 2:
                                reload_webs();
                                break;
                            case 3:
                                reset_web_rules();
                                break;
                            case 4:
                                reset_web_rules();
                                break;
                            case 5:
                                reset_global_rules();
                                break;
                            case 6:
                                reset_sys_rules();
                                break;
                            case 7:
                                reset_white_sysrules();
                                break;
                        }
                    } else {
                        // 等待10s
                        sleep($sleep);
                    }
                } catch (ConnectionException $exception) {
                    $this->error('同步命令失败1' . $exception->getMessage());
                    // echo 'syncmd error =>' . $exception->getMessage();
                    \Log::error('syncmd error =>' . $exception->getMessage());
                    break;
                } catch (\Exception $exception) {
                    if ($data['fail'] > 3) {
                        $data['ip']          = env('LOCAIP');
                        $data['failed_time'] = date('Y-m-d H:i:s');
                        $data['exception']   = $exception->getMessage();
                        FailedSyncmd::create($data);
                        $this->error('同步命令失败2' . $exception->getMessage() . json_encode($data));
                    } else {
                        $data['fail'] += 1;
                        $SynCmdRedis->lpush(config('modsecurity.syn_list_key'), json_encode($data, JSON_UNESCAPED_UNICODE));
                    }
                    continue;
                }
            }
        }
    }
}
