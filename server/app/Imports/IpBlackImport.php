<?php

namespace App\Imports;

use App\Models\IpBlack;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class IpBlackImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $collection_array = $collection->toArray();
        $time             = date('Y-m-d H:i:s');
        $admin_id         = request()->input('user_id', 0);
        $data             = [];
        foreach ($collection_array as $k => $v) {
            if ($v['ip']) {
                $data[$k]['ip']       = $v['ip'];
                $data[$k]['admin_id'] = $admin_id;
                $data[$k]['type']     = 1;
                $data[$k]['reason']   = $v['note'] ?? '';
                $second               = timetosecond($v['time']);
                if ($second > 0) {
                    $data[$k]['expire_time'] = time() + $second;
                } else {
                    $data[$k]['expire_time'] = 0;
                }
                $data[$k]['created_at'] = $time;
                $data[$k]['updated_at'] = $time;
            }
        }
        IpBlack::insertOrIgnore(array_values($data));
        $kps = array_column($data, 'ip');
        $kps = IpBlack::whereIn('ip', $kps)->pluck('ip')->toArray();
        $kps = array_flip($kps);
        // event(new SynIptablesActionEvent(0, $kps));
        // \Artisan::call('command:iptables-reload');

    }
}
