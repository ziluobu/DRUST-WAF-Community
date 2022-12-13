<?php

namespace App\Http\Controllers\Api;

use App\Models\Group;
use App\Models\MonthReport;
use App\Models\Web;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ZdyReport;
use App\Exceptions\ApiException;

class ReportController extends BaseApiController
{

    private $customAttributes = [
        'beginTime' => '开始时间',
        'endTime'   => '结束时间',
        'web_ids'   => '站点',
        'id'        => '文件',
    ];

    //自定义报告
    public function getReportZdyTask(Request $request)
    {
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $orderByColumn = 'created_at';
        $isAsc         = $request->input('isAsc', 'desc');
        $list          = ZdyReport::select(['id', 'created_at', 'username', 'begin_time', 'end_time', 'web_ids', 'note', 'status', 'reportname'])->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();
        $data          = Web::select(['web_name', 'id', 'web_port'])->get()->toArray();
        $webs          = [];
        foreach ($data as $v) {
            $webs[$v['id']] = $v['web_name'] . ':' . $v['web_port'];
        }
        foreach ($list as $k => $v) {
            if ($list[$k]['web_ids']) {
                foreach ($list[$k]['web_ids'] as $i => $id) {
                    $list[$k]['web_ids'][$i] = $webs[$id] ?? '-';
                }
            }
        }
        return $this->success(['list' => $list, 'count' => ZdyReport::count('id')]);
    }

    public function reportZdyDown(Request $request, ZdyReport $report)
    {
        if ($report->status != 1) {
            throw new ApiException('报告生成中~');
        }
        return response()->download(storage_path('app/report/') . $report->path,
            $report->reportname,
            ['Access-Control-Expose-Headers' => 'Content-Disposition']
        );
    }

    public function reportMonthDown(Request $request, MonthReport $report)
    {
        if ($report->status != 1) {
            throw new ApiException('报告生成中~');
        }
        return response()->download(storage_path('app/report/') . $report->path,
            $report->reportname,
            ['Access-Control-Expose-Headers' => 'Content-Disposition']
        );
    }

    public function addReportZdyTask(Request $request)
    {
        $rules   = [
            'beginTime' => 'required_with:endTime|date|before:endTime|bail',
            'endTime'   => 'required_with:beginTime|date|before_or_equal:today|bail',
            'web_ids'   => 'required|array|bail',
        ];
        $web_ids = Web::pluck('id')->toArray();
        $web_ids = array_intersect($web_ids, $request->input('web_ids'));
        if (!$web_ids) {
            throw new ApiException('站点错误');
        }
        if (Carbon::parse($request->input('endTime'))->diffInDays(Carbon::parse($request->input('beginTime'))) > 31) {
            throw new ApiException('时间跨度不可超过一个月');
        }
        $beginTime = date('Y-m-d H_i_s', strtotime($request->input('beginTime')));
        $endTime   = date('Y-m-d H_i_s', strtotime($request->input('endTime')));
        $this->validator($rules, $this->customAttributes);

        $group_name            = Group::where('id', $request->input('user_group_id'))->value('group_name') ?: '管理员';
        $ZdyReport             = new ZdyReport();
        $ZdyReport->username   = $request->input('loginUsername');
        $ZdyReport->admin_id   = $request->input('user_id');
        $ZdyReport->reportname = "{$group_name}_防护报告 ({$beginTime}_{$endTime}).docx";
        $ZdyReport->title      = '站点安全防护报告';
        $ZdyReport->web_ids    = $web_ids;
        $ZdyReport->group_id   = $request->input('user_group_id');
        $ZdyReport->begin_time = $request->input('beginTime');
        $ZdyReport->end_time   = $request->input('endTime');
        $ZdyReport->note       = $request->input('note', '');
        $ZdyReport->status     = 0;
        $ZdyReport->save();
        return $this->success();
    }

    public function contractList(Request $request)
    {
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $orderByColumn = 'created_at';
        $isAsc         = $request->input('isAsc', 'desc');
        $list          = MonthReport::select(['id', 'reportname', 'status', 'updated_at'])->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();
        return $this->success(['list' => $list, 'count' => MonthReport::count('id')]);
    }

    public function zdydestroy(Request $request, ZdyReport $zdyReport)
    {
        if ($zdyReport->status != 1) {
            throw new ApiException('报告生成中~');
        } else {
            if ($zdyReport->path) {
                \Storage::disk('report')->delete($zdyReport->path);
            }
        }
        $zdyReport->delete();
        return $this->success();
    }

    public function monthdestroy(Request $request, MonthReport $monthReport)
    {
        if ($monthReport->status != 1) {
            throw new ApiException('报告生成中~');
        } else {
            if ($monthReport->path) {
                \Storage::disk('report')->delete($monthReport->path);
            }
        }
        $monthReport->delete();
        return $this->success();
    }

}
