<?php

namespace App\Jobs;

use App\Http\Extensions\CreateReport;
use App\Models\Group;
use App\Models\ZdyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ZdyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**php artisan queue:work --daemon  --queue=zdyreport --delay=3 --sleep=3 --tries=3
     * 任务失败前允许的最大异常数
     *
     * @var int
     */
    public $maxExceptions = 3;
    /**
     * 在超时之前任务可以运行的秒数
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            //实例化 phpword 类
            //指定事先制作好的模板文件路径
            $ZdyReport = ZdyReport::where('id', $this->data['id'])->first();

            $groupName         = Group::where('id', $ZdyReport->group_id)->value('group_name') ?: '管理员';
            $CreateReport      = new CreateReport(
                $groupName,
                $ZdyReport->reportname,
                $ZdyReport->begin_time,
                $ZdyReport->end_time,
                base_path("template/report.docx"),
                $ZdyReport->web_ids,
                $ZdyReport->title
            );
            $ZdyReport->path   = $CreateReport->run();
            $ZdyReport->status = 1;
            $ZdyReport->save();
            echo $groupName . '自定义报告生成成功' . PHP_EOL;
            return;
        } catch (\Exception $exception) {
            Log::error('zdyreport error=>' . $exception->getMessage(), $this->data);
            echo $groupName . '自定义报告生成失败' . $exception->getMessage() . PHP_EOL;
            return;
        }
    }


    /**
     * 任务未能处理
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $ZdyReport         = ZdyReport::where('id', $this->data['id'])->first();
        $ZdyReport->status = 2;
        $ZdyReport->save();
        Log::error('zdyreport faild=>' . $exception->getMessage(), $this->data);
    }

}
