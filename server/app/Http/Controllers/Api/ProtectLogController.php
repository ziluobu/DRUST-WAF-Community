<?php

namespace App\Http\Controllers\Api;

use App\Models\Web;
use Basemkhirat\Elasticsearch\Facades\ES;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProtectLogController extends BaseApiController
{    //防御日志列表
    protected $total = 10000;

    public function index(Request $request)
    {
        // $list = ES::raw()->search([
        //     "index" => "apachelog*",
        //     "body"  => [
        //         "aggs" => [
        //             "genres" => [
        //                 "terms" => [
        //                     "field" => "geoip.timezone.keyword",
        //                     "size"  => "5",
        //                 ]
        //             ]
        //         ],
        //         'size' => 0
        //     ]
        // ]);
        // $list = ES::raw()->search([
        //     "index" => "apachelog*",
        //     "body"  => [
        //         "aggs" => [
        //             "groupDate" => [
        //                 "date_histogram" => [
        //                     "field"         => "@timestamp",
        //                     "interval"      => "10m",
        //                     "format"        => "yyyy-MM-dd HH",
        //                     "min_doc_count" => 0,
        //                 ]
        //             ]
        //         ],
        //         'size' => 0
        //     ]
        // ]);
        // dd($list);
        // 分页、筛选数据
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'Time');
        $orderByColumn = '@timestamp';
        if ($orderByColumn == 'id') {
            $orderByColumn = '_id';
        }
        $isAsc     = $request->input('isAsc', 'desc');
        $Host      = $request->input('Hostname');
        $attack_ip = $request->input('attack_ip');
        $status    = $request->input('status');
        $method    = $request->input('method');
        $type_id   = $request->input('type_id');
        $beginTime = $request->input('beginTime');
        $endTime   = $request->input('endTime');

        $maxPage = ceil($this->total / $pageSize);
        $pageNum = $pageNum > $maxPage ? $maxPage : $pageNum;

        $start = ($pageNum - 1) * $pageSize;

        $beginTime = $beginTime ? ($beginTime > strtotime('-7 days') ? $beginTime : strtotime('-7 days')) : strtotime('-7 days');
        $beginTime = isset($beginTime) ? Carbon::parse($beginTime)->toIso8601String() : null;
        $endTime   = isset($endTime) ? Carbon::parse($endTime)->toIso8601String() : null;

        $group_id = $request->input('user_group_id');
        $query    = ES::index("dxwaflog*")
            ->orderBy($orderByColumn, $isAsc)
            ->select("Hostname", "Url", "PartA", "PartB", "PartC", "PartF", "PartH", "status", "attack_ip", "method", "rule_id", "type_id", "type_name", "msg", "Time")
            ->whereNot('type_id', 0);
        if ($group_id > 0) {
            $web_names = Web::where('group_id', $group_id)->pluck('web_name')->toArray();
            $query->whereIn('Hostname.keyword', $web_names);
        }
        if ($Host) {
            $query->where('Hostname.keyword', 'like', $Host);
        }
        if ($attack_ip) {
            $query->where('attack_ip', $attack_ip);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($method) {
            $query->where('method', strtoupper($method));
        }
        if ($type_id) {
            $query->where('type_id', $type_id);
        }
        if ($beginTime && $endTime) {
            $query->whereBetween('@timestamp', [$beginTime, $endTime]);
        } elseif ($beginTime) {
            $query->where('@timestamp', '>=', $beginTime);
        } elseif ($endTime) {
            $query->where('@timestamp', '<=', $endTime);
        }
        $total = $query->count();
        $query = $query->take($pageSize)->skip($start);
        // dd($query->query());
        $response = $query->get()->toArray();
        // "PartA", "PartB", "PartC", "PartF", "PartH"
        foreach ($response as $k => $v) {
            // unset($v['_index'], $v['_type'], $v['_score']);
            $response[$k]          = $v;
            $response[$k]['PartA'] = str_replace('\n', PHP_EOL, $v['PartA']);
            $response[$k]['PartB'] = str_replace('\n', PHP_EOL, $v['PartB']);
            $response[$k]['PartC'] = (str_replace('\n', PHP_EOL, $v['PartC']));
            $response[$k]['PartF'] = str_replace('\n', PHP_EOL, $v['PartF']);
            $response[$k]['PartH'] = str_replace('\n', PHP_EOL, $v['PartH']);
        }
        return $this->success(['list' => $response, 'count' => $total]);
    }

}
