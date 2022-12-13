<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BaseApiController extends Controller
{

    protected $excepts = [
        'api/*/upload',
        'api/*/import',
    ];

    public function __construct()
    {
        $this->checkHeaders();
    }

    private function checkHeaders()
    {
        foreach ($this->excepts as $except) {
            if (\request()->is($except)) {
                return true;
            }
        }
        $method      = strtoupper(\request()->method());
        $contenttype = \request()->getContentType();
        if ($method == 'POST' || $method == 'PUT') {
            if ($contenttype != 'json') {
                throw new ApiException('content-type格式错误');
            }
        }
    }


    protected function validator($rules = [], $customAttributes = [], $message = [])
    {
        $param = array_filter(request()->all(), function ($v) {
            if (!is_null($v)) {
                return true;
            }
            return false;
        });

        $rules     = array_merge($rules, [
            'timestamp' => 'required|bail|numeric',
            // 'sign'      => 'required|bail',
        ]);
        $validator = Validator::make($param, $rules, $message, $customAttributes);

        if ($validator->fails()) {
            //验证不通过
            $error = $validator->errors()->first();
            $error = $error ?: '提交数据错误';
            throw new ApiException($error);
        }
    }

    protected function uploadFile($path, $file, $type = 1)
    {
        $types    = [
            1 => 'file',
            2 => 'img'
        ];
        $path     = $types[$type] . '/' . $path . '/' . date('Ymd');
        $filePath = \Storage::putFile($path, $file);
        return $filePath;
    }


    protected function success($data = [], $msg = '请求成功', $code = 2000)
    {
        return response()
            ->json([
                'msg'  => $msg,
                'code' => $code,
                'data' => recursive_str($data)
            ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

}
