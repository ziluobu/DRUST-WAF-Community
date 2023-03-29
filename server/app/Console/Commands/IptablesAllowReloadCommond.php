<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class IptablesAllowReloadCommond extends Command
{
    /*
     * 同步白名单
     * */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:iptables-allow-reload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description iptables-allow-reload';

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始同步白名单iptables....');
        try {
            // echo "同步白名单iptables...." . PHP_EOL;
            $redis = app('redis')->connection('iptables');

            $allow_add_ips = $redis->hkeys('allow_add_ip');
            if ($allow_add_ips) {
                $cmd = '';
                foreach ($allow_add_ips as $ip) {
                    $cmd .= " ipset add ipallow $ip ;";
                }
                exec("sudo" . rtrim($cmd, ';'), $output, $code);
                if ($code == 1) {
                    $this->error('ipset add ipallow error');
                    // echo "ipset add ipallow error" . PHP_EOL;
                } else {
                    $this->info('ipset add ipallow success');
                    // echo "ipset add ipallow success" . PHP_EOL;
                }
            }
            $allow_del_ips = $redis->hkeys('allow_del_ip');
            if ($allow_del_ips) {
                $cmd = '';
                foreach ($allow_del_ips as $ip) {
                    $cmd .= " ipset del ipallow $ip ;";
                }
                exec("sudo" . rtrim($cmd, ';'), $output, $code);
                if ($code == 1) {
                    $this->error('ipset del ipallow error');
                    // echo "ipset del ipallow error" . PHP_EOL;
                } else {
                    $this->info('ipset del ipallow success');
                    // echo "ipset del ipallow success" . PHP_EOL;
                }
            }

            exec("sudo service ipset save", $output, $code);
            if ($code == 1) {
                $this->error('service ipset save error');
                // echo "service ipset save error" . PHP_EOL;
            } else {
                $this->info('service ipset save success');
                // echo "service ipset save success" . PHP_EOL;
            }
            $redis->del(['allow_add_ip', 'allow_del_ip']);
            if ($allow_add_ips || $allow_del_ips) {
                //同步到白名单文件
                reload_allow_ips();
            }
            $this->info('检测结束');
        } catch (\Exception $exception) {
            $this->error('同步白名单iptables失败' . $exception->getMessage());
            \Log::error('iptables-allow-reload =>' . $exception->getMessage());
            // echo 'iptables-allow-reload =>' . $exception->getMessage() . PHP_EOL;
        }
    }
}
