<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SynIptablesActionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 同步黑白名单事件
     */

    protected $keys = [
        'black_add_ip',
        'black_del_ip',
        'allow_add_ip',
        'allow_del_ip',
    ];

    public $type;
    public $ip;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type,array $ip)
    {
        $this->ip   = $ip;
        $this->type = $this->keys[$type];
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
