<?php

namespace App\Http\Controllers\Api;

use App\Exports\OpertlogExport;
use App\Models\Opertlog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OpertlogController extends BaseApiController
{
    //
    public function index(Request $request)
    {
        [$list, $count] = $this->getList($request);
        return $this->success(['list' => $list, 'count' => $count]);
    }

    public function destroy($id)
    {
        $ids = explode(',', $id);
        $ids = array_filter($ids, function ($v) {
            if (is_numeric($v)) {
                return true;
            }
            return false;
        });
        if (is_array($ids)) {
            Opertlog::destroy($ids);
        }
        return $this->success();
    }

    public function trash()
    {
        Opertlog::truncate();
        return $this->success();
    }

    public function export(Request $request)
    {
        return false;
        [$list, $count] = $this->getList($request);
        $filePath = 'export/Opertlog.xlsx';
        Excel::store(new OpertlogExport($list), $filePath);
        $url = \Storage::url($filePath);
        return $this->success(['url' => $url]);
    }

    private function getList(Request $request)
    {
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $isAsc         = $request->input('isAsc', 'desc');

        $module         = $request->input('module');
        $username       = $request->input('username');
        $request_method = $request->input('request_method');
        $api_status     = $request->input('api_status');
        $beginTime      = $request->input('beginTime');
        $endTime        = $request->input('endTime');
        $rules          = [
            'beginTime' => 'required_with:endTime|date|before:endTime|bail',
            'endTime'   => 'required_with:beginTime|date|after:beginTime|bail',
            'status'    => 'sometimes|required|bail|in:0,1',
        ];
        $this->validator($rules);

        $Query      = Opertlog::when($module, function ($query) use ($module) {
            return $query->where('module', $module);
        })->when($username, function ($query) use ($username) {
            return $query->where('username', 'like', "%$username%");
        })->when(isset($request_method), function ($query) use ($request_method) {
            return $query->where('request_method', $request_method);
        })->when(isset($api_status), function ($query) use ($api_status) {
            if ($api_status) {
                return $query->where('api_status', 2000);
            }
            return $query->where('api_status', '!=', 2000);
        })->when($beginTime && $endTime, function ($query) use ($beginTime, $endTime) {
            return $query->whereBetween('created_at', [$beginTime, $endTime]);
        });
        $countQuery = clone $Query;
        $list       = $Query->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();
        $count      = $countQuery->count('id');
        return [$list, $count];
    }
}

