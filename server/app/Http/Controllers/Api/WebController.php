<?php

namespace App\Http\Controllers\Api;

use App\Events\SynCmdEvent;
use App\Models\Web;
use App\Rules\CheckExt;
use Illuminate\Http\Request;

class WebController extends BaseApiController
{
    private $customAttributes = [
        'web_sysname'         => '系统名称',
        'web_port'            => '源端口',
        'web_name'            => '防护域名',
        'source_ip'           => '源站地址',
        'dst_port'            => '目的端口',
        'is_https'            => '是否开启https',
        'proxy_catefile'      => '证书文件',
        'proxy_catekeyfile'   => '密钥文件',
        'proxy_catechainfile' => '证书链文件',
        'protect_status'      => '防护模式',
        'group_id'            => '所属单位',
    ];

    //网站列表
    public function index(Request $request)
    {
        // 分页、筛选数据
        $pageSize      = $request->input('pageSize', 20);
        $pageNum       = $request->input('pageNum', 1);
        $orderByColumn = $request->input('orderByColumn', 'web_id');
        $isAsc         = $request->input('isAsc', 'desc');
        $group_id      = $request->input('group_id');
        $web_name      = $request->input('web_name');
        //请求数据对象object
        $WebQuery = Web::when($group_id, function ($query) use ($group_id) {
            return $query->where('group_id', $group_id);
        })->when(isset($web_name), function ($query) use ($web_name) {
            return $query->where('web_name', $web_name);
        });
        $Query    = clone $WebQuery;
        //获取数据进行排序 offset设置从第几个开始，limit规定取多少个达到分页效果
        $list = $WebQuery->orderBy($orderByColumn, $isAsc)
            ->offset(($pageNum - 1) * $pageSize)->limit($pageSize)
            ->get()->toArray();
        foreach ($list as $k => $v) {
            if ($v['is_https']) {
                $url = "https://" . $v['web_name'] . ($v['web_port'] != '443' ? (':' . $v['web_port']) : '');
            } else {
                $url = "http://" . $v['web_name'] . ($v['web_port'] != '80' ? (':' . $v['web_port']) : '');
            }
            $list[$k]['link_url'] = $url;
        }
        return $this->success(['list' => $list, 'count' => $Query->count('id')]);
    }

    public function searchList()
    {
        $data = Web::select(['web_name', 'id', 'web_port'])->get()->toArray();
        $list = [];
        foreach ($data as $k => $v) {
            $list[$k]['id']   = $v['id'];
            $list[$k]['name'] = $v['web_name'] . ':' . $v['web_port'];
        }

        return $this->success($list);
    }

    //新增网站
    public function store(Request $request)
    {
        $rules = [
            //'web_sysname' => '',
            'web_name'            => ['required', 'bail', 'regex:/[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+\.?/'],
            'web_port'            => 'required|bail|numeric',
            'source_ip'           => 'required|bail|ip',
            'dst_port'            => 'required|bail|numeric',
            'is_https'            => 'required|bail|numeric|in:0,1',
            'proxy_catefile'      => 'required_if:is_https,1|ends_with:.crt|bail',
            'proxy_catekeyfile'   => 'required_if:is_https,1|ends_with:.key|bail',
            'proxy_catechainfile' => 'required_if:is_https,1|ends_with:.crt|bail',
            'protect_status'      => 'required|bail|numeric|in:0,1',
            'group_id'            => 'required|exists:group,id|bail',
        ];

        $this->validator($rules, $this->customAttributes);
        $web                      = new Web();
        $web->web_sysname         = $request->input('web_sysname', '');
        $web->web_port            = $request->input('web_port', 80);
        $web->web_name            = $request->input('web_name');
        $web->source_ip           = $request->input('source_ip');
        $web->dst_port            = $request->input('dst_port', 80);
        $web->is_https            = $request->input('is_https');
        $web->proxy_catefile      = $request->input('proxy_catefile', '');
        $web->proxy_catekeyfile   = $request->input('proxy_catekeyfile', '');
        $web->proxy_catechainfile = $request->input('proxy_catechainfile', '');
        $web->protect_status      = $request->input('protect_status');
        $web->group_id            = $request->input('group_id');
        $web->save();
        return $this->success();
    }

    //删除
    public function destroy(Web $web)
    {
        $web->delete();
        return $this->success();
    }

    //查询详情
    public function show(Request $request, Web $web)
    {
        return $this->success($web->toArray());
    }

    //更新网站
    public function update(Request $request, Web $web)
    {
        $rules = [
            //'web_sysname' => '',
            'web_name'            => ['required', 'bail', 'regex:/[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+\.?/'],
            'web_port'            => 'required|bail|numeric',
            'source_ip'           => 'required|bail|ip',
            'dst_port'            => 'required|bail|numeric',
            'is_https'            => 'required|bail|numeric|in:0,1',
            'proxy_catefile'      => 'required_if:is_https,1|ends_with:.crt|bail',
            'proxy_catekeyfile'   => 'required_if:is_https,1|ends_with:.key|bail',
            'proxy_catechainfile' => 'required_if:is_https,1|ends_with:.crt|bail',
            'protect_status'      => 'required|bail|numeric|in:0,1',
            'group_id'            => 'required|exists:group,id|bail',
        ];

        $this->validator($rules, $this->customAttributes);
        $web->web_sysname         = $request->input('web_sysname', '');
        $web->web_port            = $request->input('web_port', 80);
        $web->web_name            = $request->input('web_name');
        $web->source_ip           = $request->input('source_ip');
        $web->dst_port            = $request->input('dst_port', 80);
        $web->is_https            = $request->input('is_https');
        $web->proxy_catefile      = $request->input('proxy_catefile', '');
        $web->proxy_catekeyfile   = $request->input('proxy_catekeyfile', '');
        $web->proxy_catechainfile = $request->input('proxy_catechainfile', '');
        $web->protect_status      = $request->input('protect_status');
        $web->group_id            = $request->input('group_id');
        $web->save();
        return $this->success();
    }

    public function upload(Request $request)
    {
        $type             = $request->input('type');
        $file_rule        = ['required', 'bail', 'file', new CheckExt($type)];
        $rules            = [
            'type' => 'required|in:1,2,3|bail',
            'file' => $file_rule
        ];
        $customAttributes = [
            'file' => '文件',
        ];
        $this->validator($rules, $customAttributes);
        $filePath = \Storage::disk('ssl')->putFileAs(
            '/',
            $request->file('file'),
            \Str::uuid() . '.' . $request->file('file')->getClientOriginalExtension());
        //        $path     = \Storage::disk('ssl')->url($filePath);
        return $this->success(['path' => $filePath]);
    }

    //todo 待完成
    public function syncConfig()
    {
        reload_webs();
        event(new SynCmdEvent(2));
        return $this->success();
    }

    //日报
    //todo 待完成
    public function dailyReport()
    {

    }
}
