<?php

namespace App\Listeners;

use App\Events\SynIptablesActionEvent;
use Predis\Client;

class SynIptablesListenner
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
     * @param SynIptablesActionEvent $event
     * @return void
     */
    public function handle(SynIptablesActionEvent $event)
    {
        try {
            $redis_connects = config('database.redis.iptables-clusters');
            foreach ($redis_connects as $connect) {
                if ($connect['host']) {
                    $redis = new Client($connect);
                    $redis->hmset($event->type, $event->ip);
                }
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}
