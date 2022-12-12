<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SynCmdEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 同步系统指令
     * 同步黑白名单
     * 同步站点
     * 同步规则
     */
    /**
     * Create a new event instance.
     *
     * @return void
     */

    /**
     * @var int 事件类型
     * 0 同步黑名单
     * 1 同步白名单
     * 2 同步站点
     * 3 同步网站规则
     * 4 同步国外IP防御规则
     * 5 同步全局规则
     * 6 同步系统规则
     * 7 同步自定义系统规则
     */
    public $type;

    public function __construct(int $type)
    {
        //
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
