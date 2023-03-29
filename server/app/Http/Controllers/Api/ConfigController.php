<?php

namespace App\Http\Controllers\Api;

use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends BaseApiController
{
    //
    public function index(Request $request)
    {
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'id');
        $isAsc         = $request->input('isAsc', 'desc');
        $name          = $request->input('name');
        $key           = $request->input('key');
        $value         = $request->input('value');

        $Query = Config::when($name, function ($query) use ($name) {
            return $query->where('name', 'like', "%$name%");
        })->when($key, function ($query) use ($key) {
            return $query->where('key', 'like', "%$key%");
        })->when($value, function ($query) use ($value) {
            return $query->where('value', 'like', "%$value%");
        });
        $countQuery = clone $Query;
        $list  = $Query->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();
        return $this->success(['list' => $list, 'count' => $countQuery->count('id')]);
    }

    public function store(Request $request)
    {
        $rules = [
            'type'  => 'required|in:0,1,2|bail',
            'name'  => 'required|bail',
            'key'   => 'required|unique:config,key|alpha_dash|bail',
            'value' => 'required|bail',
        ];
        $this->validator($rules);
        $Config        = new Config();
        $param         = $request->all();
        $Config->type  = $param['type'];
        $Config->name  = $param['name'];
        $Config->key   = $param['key'];
        $Config->value = $param['value'];
        $Config->save();
        return $this->success();
    }

    public function show(Request $request, Config $config)
    {
        return $this->success($config->toArray());
    }


    public function update(Request $request, Config $config)
    {
        $rules = [
            'name'  => 'required_without:value',
            'value' => 'required_without:name',
        ];
        $this->validator($rules);
        if ($request->input('name')) {
            $config->name = $request->input('name');
        }
        if ($request->input('value')) {
            $config->value = $request->input('value');
        }
        $config->save();
        return $this->success();
    }

    public function destroy(Request $request, $id)
    {
        $this->validator();
        $ids = explode(',', $id);
        $ids = array_filter($ids, function ($v) {
            if (is_numeric($v)) {
                return true;
            }
            return false;
        });
        if (is_array($ids)) {
            Config::destroy($ids);
        }
        return $this->success();
    }

    public function upload(Request $request)
    {
        $type = $request->input('type');
        if ($type == 1) {
            $file_rule = 'required|bail|mimes:json,doc,docx,xls,xlsx';
        } else {
            $file_rule = 'required|bail|image';
        }
        $rules            = [
            'type' => 'required|in:1,2|bail',
            'file' => $file_rule
        ];
        $customAttributes = [
            'file' => '文件',
        ];
        $this->validator($rules, $customAttributes);
        $path = $this->uploadFile('config', $request->file('file'), $type);
        $path = \Storage::url($path);
        return $this->success(['path' => $path]);
    }
}
