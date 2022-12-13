<?php

namespace App\Console\Commands;

use App\Models\IpAllow;
use Illuminate\Console\Command;

class IpallowInitCommond extends Command
{

    /*
     * 初始化白名单，刚部署时运行
     * */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ipallow-init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command ipallow-init';

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
            $this->info('初始化白名单....');
            $ips = IpAllow::where('expire_time', '>', time())
                ->pluck('ip')->toArray();
            if ($ips) {
                // $ips_str = "create ipallow hash:net family inet hashsize 1024 maxelem 1000000" . PHP_EOL;
                $cmd = "ipset flush ipallow && ";
                foreach ($ips as $ip) {
                    $cmd .= " ipset add ipallow $ip ;";
                }
                $cmd = rtrim($cmd, ';');
                exec($cmd, $output, $code);
                if ($code == 1) {
                    throw new \Exception(json_encode($output));
                } else {
                    exec("sudo service ipset save", $output, $code);
                }
            }
            $this->info('初始化白名单成功');
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
