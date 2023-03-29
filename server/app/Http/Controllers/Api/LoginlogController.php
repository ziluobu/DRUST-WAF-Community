<?php

namespace App\Http\Controllers\Api;

use App\Exports\LoginlogExport;
use App\Models\Loginlog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LoginlogController extends BaseApiController
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
            Loginlog::destroy($ids);
        }
        return $this->success();
    }

    public function trash()
    {
        Loginlog::truncate();
        return $this->success();
    }

    public function export(Request $request)
    {
        [$list, $count] = $this->getList($request);
        $filePath = 'export/loginlog.xlsx';
        Excel::store(new LoginlogExport($list), $filePath);
        $url = \Storage::url($filePath);
        return $this->success(['url' => $url]);
    }

    private function getList(Request $request)
    {
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $isAsc         = $request->input('isAsc', 'desc');
        $login_name    = $request->input('loginName');
        $ipaddr        = $request->input('ipaddr');
        $status        = $request->input('status');
        $beginTime     = $request->input('beginTime');
        $endTime       = $request->input('endTime');
        $rules         = [
            'beginTime' => 'required_with:endTime|date|before:endTime|bail',
            'endTime'   => 'required_with:beginTime|date|after:beginTime|bail',
            'status'    => 'sometimes|required|bail|in:0,1',
        ];
        $this->validator($rules);

        $Query      = Loginlog::when($login_name, function ($query) use ($login_name) {
            return $query->where('login_name', 'like', "%$login_name%");
        })->when(isset($status), function ($query) use ($status) {
            return $query->where('status', $status);
        })->when($ipaddr, function ($query) use ($ipaddr) {
            return $query->where('ipaddr', 'like', "%$ipaddr%");
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

