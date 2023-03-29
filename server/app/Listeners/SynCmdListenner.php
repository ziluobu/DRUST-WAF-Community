<?php

namespace App\Listeners;

use App\Events\SynCmdEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Predis\Client;

class SynCmdListenner
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param SynCmdEvent $event
     * @return void
     */

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
    public function handle(SynCmdEvent $event)
    {
        return
        $types = [
            '同步黑名单',
            '同步白名单',
            '同步站点',
            '同步网站规则',
            '同步国外IP防御规则',
            '同步全局规则',
            '同步系统规则',
            '同步自定义系统规则',
        ];

        if (!env('IS_MASTER') || env('APP_ENV') != 'production') {
            return;
        }
        try {
            $redis_connects = config('database.redis.syncmd-clusters');
            $data           = [
                'type'        => $event->type,
                'name'        => $types[$event->type] ?? '',
                'fail'        => 0,
                'create_time' => date('Y-m-d H:i:s'),
            ];
            foreach ($redis_connects as $connect) {
                if ($connect['host']) {
                    $redis = new Client($connect);
                    $redis->lpush(config('modsecurity.syn_list_key'), [json_encode($data, JSON_UNESCAPED_UNICODE)]);
                }
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
        //
    }
}
