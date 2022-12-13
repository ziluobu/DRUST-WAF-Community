<?php

namespace App\Http\Controllers\Api;

use App\Http\Extensions\CreateReport;
use App\Models\Group;
use App\Models\MonthReport;
use App\Models\Web;
use Carbon\Carbon;

class WordController extends BaseApiController
{

    public function index()
    {

        $groups = Group::get()->toArray();
        if ($groups) {
            echo '开始生成月报' . PHP_EOL;
            $geneDate = date('Ymd');
            // $month         = Carbon::parse('-1 months');
            $month      = Carbon::now();
            $startMonth = $month->startOfMonth()->toDateTimeString();
            $endMonth   = $month->endOfMonth()->toDateTimeString();

            foreach ($groups as $group) {

                $dateStartString = $month->startOfMonth()->toDateString();
                $dateEndString   = $month->endOfMonth()->toDateString();
                $reportName      = $group['group_name'] . '_' . $month->month . '月防护报告 (' . $dateStartString . '_' . $dateEndString . ').docx';

                $MonthReport = MonthReport::firstOrNew(['reportname' => $reportName]);
                if ($MonthReport->path) {
                    \Storage::disk('report')->delete($MonthReport->path);
                }
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
                    base_path("template/month.docx"),
                    Web::where('group_id', $group['id'])->groupBy('web_name')->pluck('id')->toArray()
                );
                $MonthReport->path   = $CreateReport->run();
                $MonthReport->status = 1;
                $MonthReport->save();
            }

            echo "月报生成完成" . PHP_EOL;
        }
    }

}
