<?php

namespace App\Console\Commands;

use App\Events\SynIptablesActionEvent;
use App\Models\IpAllow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class IpAllowCommond extends Command
{

    /*
     * 删除过期白名单
     * */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:IpAllow-delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'IpAllow-delete';

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
        $this->info('开始检测过期白名单IP');
        try {
            //查找过期ip
            $expire_list = IpAllow::where('expire_time', '<', time())
                ->pluck('ip', 'id')->toArray();
            if ($expire_list) {
                event(new SynIptablesActionEvent(3, array_flip($expire_list)));
                IpAllow::destroy(array_keys($expire_list));
                $this->info('检测到过期白名单');
            }
            $this->info('检测结束');
        } catch (\Exception $exception) {
            $this->error('检测过期白名单失败' . $exception->getMessage());
            // echo 'command:IpAllow-delete error' . $exception->getMessage();
            Log::error('command:IpAllow-delete error' . $exception->getMessage());
        }
    }
}
