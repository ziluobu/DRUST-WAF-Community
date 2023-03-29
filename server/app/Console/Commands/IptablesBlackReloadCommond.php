<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class IptablesBlackReloadCommond extends Command
{

    /*
     * 同步黑名单
     * */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:iptables-black-reload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description iptables-black-reload';

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
        $this->info('同步黑名单iptables....');
        try {
            // echo "同步黑名单iptables...." . PHP_EOL;
            $redis         = app('redis')->connection('iptables');
            $black_add_ips = $redis->hkeys('black_add_ip');
            if ($black_add_ips) {
                $cmd = '';
                foreach ($black_add_ips as $ip) {
                    $cmd .= " ipset add ipdeny $ip ;";
                }
                exec("sudo" . rtrim($cmd, ';'), $output, $code);
                if ($code == 1) {
                    $this->error('ipset add ipdeny error');
                    // echo "ipset add ipdeny error" . PHP_EOL;
                } else {
                    $this->info('ipset add ipdeny success');
                    // echo "ipset add ipdeny success" . PHP_EOL;
                }
            }
            $black_del_ips = $redis->hkeys('black_del_ip');
            if ($black_del_ips) {
                $cmd = '';
                foreach ($black_del_ips as $ip) {
                    $cmd .= " ipset del ipdeny $ip ;";
                }
                exec("sudo" . rtrim($cmd, ';'), $output, $code);
                if ($code == 1) {
                    $this->error('ipset del ipdeny error');
                    // echo "ipset del ipdeny error" . PHP_EOL;
                } else {
                    $this->info('ipset del ipdeny success');
                    // echo "ipset del ipdeny success" . PHP_EOL;
                }
            }
            exec("sudo service ipset save", $output, $code);

            $redis->del(['black_add_ip', 'black_del_ip']);
            $this->info('检测成功');
        } catch (\Exception $exception) {
            $this->info('同步黑名单iptables失败' . $exception->getMessage());
            \Log::error('iptables-black-reload =>' . $exception->getMessage());
            // echo 'iptables-black-reload =>' . $exception->getMessage() . PHP_EOL;
        }
    }
}
