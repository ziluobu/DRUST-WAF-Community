<?php

namespace App\Console\Commands;

use App\Models\IpBlack;
use Illuminate\Console\Command;

class IpdenyInitCommond extends Command
{
    /*
     * 初始化黑名单，刚部署时运行
     * */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ipdeny-init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command ipdeny-init';

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
        try {
            $this->info('初始化黑名单....');
            //删除黑名单中的白名单
            $ips = IpBlack::where('black_type', 0)
                ->orWhere('expire_time', '>', time())
                ->pluck('ip')->toArray();
            //create ipdeny hash:net family inet hashsize 1024 maxelem 1000000
            if ($ips) {
                $cmd = "ipset flush ipdeny && ";
                foreach ($ips as $ip) {
                    $cmd .= " ipset add ipdeny $ip ;";
                }
                $cmd = rtrim($cmd, ';');
                exec($cmd, $output, $code);
                if ($code == 1) {
                    throw new \Exception(json_encode($output));
                } else {
                    exec("sudo service ipset save", $output, $code);
                }
            }
            $this->info('初始化黑名单成功');
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
