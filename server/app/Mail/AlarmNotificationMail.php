<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class AlarmNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    //php artisan queue:work --daemon  --queue=alarm --delay=3 --sleep=3 --tries=3

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('alarm')
            ->subject("【鼎信云防护服务平台】 河南省鼎信信息安全等级测评有限公司 攻击告警");
    }

    public function failed(Throwable $exception)
    {
        Log::error('send email faild=>' . $exception->getMessage());
    }


}
