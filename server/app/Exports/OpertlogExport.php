<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OpertlogExport implements WithHeadings, WithEvents, FromArray
{

    public $list;

    public function __construct($list)
    {
        $this->list = $list;
    }

    /**
     * @return array
     */
    public function array(): array
    {
        //
        return $this->list;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            '日志ID',
            '登录名称',
            '登录地点',
            '登录地址',
            '浏览器',
            '操作系统',
            '运营商',
            '登录状态',
            '提示消息',
            '登录时间',
            '更新时间',
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [

        ];
    }
}
