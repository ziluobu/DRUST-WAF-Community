<?php

namespace App\Console\Commands;

use App\Http\Extensions\CreateReport;
use App\Models\Group;
use App\Models\MonthReport;
use App\Models\Web;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonthReportCommond extends Command
{

    /*
     * 生成月报
     * */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:monthReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
            $groups = Group::get()->toArray();
            if ($groups) {
                $this->info('开始生成月报');
                $month = Carbon::now()->startOfMonth()->subMonth();
                // $month      = Carbon::now();
                $startMonth      = $month->startOfMonth()->toDateTimeString();
                $endMonth        = $month->endOfMonth()->toDateTimeString();
                $dateStartString = $month->startOfMonth()->toDateString();
                $dateEndString   = $month->endOfMonth()->toDateString();
                foreach ($groups as $group) {
                    $reportName = $group['group_name'] . '_' . $month->month . '月防护报告 (' . $dateStartString . '_' . $dateEndString . ').docx';

                    $MonthReport = MonthReport::firstOrNew(['reportname' => $reportName]);
                    if ($MonthReport->path) {
                        \Storage::disk('report')->delete($MonthReport->path);
                    }
                    $MonthReport->title      = '站点安全防护月报';
                    $MonthReport->group_id   = $group['id'];
                    $MonthReport->begin_time = $startMonth;
                    $MonthReport->end_time   = $endMonth;
                    $MonthReport->status     = 0;
                    $MonthReport->save();

                    $CreateReport        = new CreateReport(
                        $group['group_name'],
                        $reportName,
                        $startMonth,
                        $endMonth,
                        base_path("template/report.docx"),
                        Web::where('group_id', $group['id'])->groupBy('web_name')->pluck('id')->toArray(),
                        '站点安全防护月报'
                    );
                    $MonthReport->path   = $CreateReport->run();
                    $MonthReport->status = 1;
                    $MonthReport->save();

                    $this->info("{$group['group_name']}月报生成完成");
                }
            }
        } catch (\Exception $exception) {
            Log::error('zdyreport error=>' . $exception->getMessage());
            $this->error("{$group['group_name']}月报生成失败" . $exception->getMessage());
        }
    }
}
