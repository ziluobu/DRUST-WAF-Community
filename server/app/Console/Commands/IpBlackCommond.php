<?php

namespace App\Console\Commands;

use App\Events\SynIptablesActionEvent;
use App\Models\IpBlack;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class IpBlackCommond extends Command
{
    /*
     * 删除过期黑名单
     * */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:IpBlack-delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'IpBlack-delete';

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
     * 处理过期黑名单
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始检测过期黑名单IP');
        try {
            //查找过期ip
            $expire_list = IpBlack::where('black_type', '<>', 0)
                ->where('expire_time', '<', time())
                ->pluck('ip', 'id')->toArray();
            //如果有过期ip，删除redis中的数据，并覆盖黑名单列表
            if ($expire_list) {
                event(new SynIptablesActionEvent(1, array_flip($expire_list)));
                IpBlack::destroy(array_keys($expire_list));
                echo 'IpBlack success';
                $this->info('检测到过期黑名单IP');
            }
            $this->info('检测结束');
        } catch (\Exception $exception) {
            // echo 'command:IpBlack-delete error' . $exception->getMessage();
            $this->error('检测过期黑名单失败' . $exception->getMessage());
            Log::error('command:IpBlack-delete error' . $exception->getMessage());
        }
    }

}
