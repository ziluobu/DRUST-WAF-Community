<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class MonthReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    //php artisan queue:work --daemon  --queue=month-report --delay=3 --sleep=3 --tries=3

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
        return $this->view('month-report')
            ->subject("【星海云防护服务平台】 星海安全实验室 防护月报")
            ->attach(\Storage::disk('report')->path('20220317/ee865e32-7876-409c-ac59-a2ba252b092a.docx'), ['as' => '星海安全实验室_2月防护报告 (2022-02-01_2022-02-28).docx']);
    }

    public function failed(Throwable $exception)
    {
        Log::error('send email faild=>' . $exception->getMessage());
    }


}
