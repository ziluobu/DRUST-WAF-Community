<?php

namespace App\Jobs;

use App\Models\RuleType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MlogicLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    //
    /**php artisan queue:work --daemon  --queue=mlogic --delay=3 --sleep=3 --tries=3
     * 任务失败前允许的最大异常数
     *
     * @var int
     */
    public $maxExceptions = 3;
    /**
     * 在超时之前任务可以运行的秒数
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $data;

    public function __construct($data)
    {
        //
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void|string
     */
    public function handle()
    {
        echo '===================>start' . PHP_EOL;
        try {
            $reg = '/^HTTP\/\d\.\d\s(([0-5])(?!04)\d{2})/m';
            if (!preg_match($reg, $this->data, $matches)) {
                echo 'status error' . PHP_EOL;
                return;
            } else {
                $status = $matches[1];
                unset($matches);
            }
            // $putdata     = fopen("php://input", "r");
            // $this->data        = stream_get_contents($putdata);
            $lookup      = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "Z"];
            $audit_parts = array_fill(0, sizeof($lookup), "NULL");
            // Analyze data to make sure its of the expected format
            $audit_log = explode("\n", $this->data);
            $current   = 0;
            $host      = "";
            foreach ($audit_log as $line) {
                //截取出域名(Host)单独保存，便于统计查询
                if (strpos($line, "Host") === 0) {
                    $hostline = $line;
                    $arrhost  = ["Host: "];
                    $arrtmp   = [""];
                    $host     = str_replace($arrhost, $arrtmp, $hostline);
                }
                // If we are at the beginning
                if (substr($line, 0, 2) === "--" && substr(str_replace(["\r", "\n"], '', $line), -2, 2) == "--" && substr($line, 0, 3) !== "---") {
                    $current = array_search(substr(str_replace(["\r", "\n"], '', $line), -3, 1), $lookup);
                    // We are unable to find the key
                    if ($current === false) {
                        echo "An invalid audit log part was specified this will not be saved" . PHP_EOL;
                        return;
                    }
                    $audit_parts[$current] = $line;
                } else {
                    $audit_parts[$current] = $audit_parts[$current] . '\n' . $line;
                }
            }

            $host       = str_replace(['\r', '\n', '\r\n', "\r", "\n"], '', $host);
            $host_redis = app('redis')->connection('host');

            //没有的直接过滤掉
            if (!$host_redis->exists($host)) {
                echo 'no host' . PHP_EOL;
                return;
            }
            if ($audit_parts[11] === "NULL") {
                // error_log("The format received does not appear correct", 0);
                echo "The format received does not appear correct" . PHP_EOL;
                return;
            }

            //截取出url单独保存，便于统计查询
            $regurl = "/(GET|POST|PUT|HEAD|DELETE|OPTIONS|TRACE|CONNECT).*?HTTP/";
            $arr    = [];
            preg_match($regurl, $audit_parts[1], $arr);
            $arrurl = ["GET ", "POST ", "PUT ", "HEAD ", "DELETE ", "OPTIONS ", "TRACE ", "CONNECT ", " HTTP"];
            $arrtmp = ["", "", "", "", "", "", "", "", ""];
            $url    = str_replace($arrurl, $arrtmp, $arr[0]);
            if ($url == '/favicon.ico') {
                return;
            }
            $method = $arr[1];

            //截取出访问时间单独保存，便于统计查询
            $regtime = "/\[.*?\]/";
            preg_match($regtime, $audit_parts[0], $arr);
            $mstime  = substr($arr[0], 1, 20);
            $date    = substr($mstime, 0, 11);
            $time    = substr($mstime, 12, 8);
            $day     = substr($date, 0, 2);
            $monthEn = substr($date, 3, 3);
            $year    = substr($date, 7, 4);
            switch ($monthEn) {
                case "Jan":
                    $month = "01";
                    break;
                case "Feb":
                    $month = "02";
                    break;
                case "Mar":
                    $month = "03";
                    break;
                case "Apr":
                    $month = "04";
                    break;
                case "May":
                    $month = "05";
                    break;
                case "Jun":
                    $month = "06";
                    break;
                case "Jul":
                    $month = "07";
                    break;
                case "Aug":
                    $month = "08";
                    break;
                case "Sep":
                    $month = "09";
                    break;
                case "Oct":
                    $month = "10";
                    break;
                case "Nov":
                    $month = "11";
                    break;
                case "Dec":
                    $month = "12";
                    break;
                default:
                    $month = 0;
            }
            $formattime = $year . "-" . $month . "-" . $day . " " . $time;

            $reg = '/\d{0,3}\.\d{0,3}\.\d{0,3}\.\d{0,3}/i';
            preg_match($reg, $audit_parts[0], $matches);
            $attack_ip = $matches[0];

            $this->data_H = $audit_parts[7];
            $reg          = '/\[id\s\"(\d+)\"]/';
            preg_match($reg, $this->data_H, $matches);
            $id = $matches[1] ?? 0;

            $reg = '/\[msg\s\S?\"([\s\S]+?)[\\]?\"]/';
            preg_match($reg, $this->data_H, $matches);
            $msg = $matches[1] ?? '';

            if ($id) {
                $redis = app('redis')->connection('rules-type');
                $type  = $redis->get($id);
                $type  = is_numeric($type) ? $type : 0;
            } else {
                if (\Str::startsWith($status, '4') || \Str::startsWith($status, '5')) {
                    $type = 0;
                } else {
                    echo 'no rule id' . PHP_EOL;
                    return;
                }
            }

            $LogstashRedis = app('redis')->connection('logstash');

            $type_name = '';
            if ($type) {
                if ($type == '590002') {
                    $type_name = '黑名单';
                } elseif ($type == '590000') {
                    $type_name = '国外IP阻断';
                } else {
                    $type_name = RuleType::where('id', $type)->value('name');
                }
            }

            if (isset($audit_parts[8]) && $audit_parts[8] && $audit_parts[8] != 'NULL') {
                $partC = $audit_parts[8];
            } else {
                $partC = $audit_parts[2];
            }
            $logstash_data = [
                'tag_MessageType' => 'dxwaflog',
                'Hostname'        => $host,
                'Url'             => $url,
                'Time'            => $formattime,
                'PartA'           => $audit_parts[0],
                'PartB'           => $audit_parts[1],
                // 'PartC'           => $audit_parts[2],
                'PartC'           => $partC,
                // 'PartD'           => $audit_parts[3],
                // 'PartE'           => $audit_parts[4],
                'PartF'           => $audit_parts[5],
                // 'PartG'           => $audit_parts[6],
                'PartH'           => $audit_parts[7],
                // 'PartI'           => $audit_parts[8],
                'PartJ'           => $audit_parts[9] ?? '',
                // 'PartK'           => $audit_parts[10],
                'status'          => (int)$status,
                'attack_ip'       => $attack_ip,
                'ipInfo'          => getgeoIpInfo($attack_ip),
                'local_ip'        => env('LOCAIP'),
                'method'          => $method,
                'rule_id'         => (int)$id ?? 0,
                'type_id'         => (int)$type,
                'type_name'       => $type_name,
                'msg'             => $msg,
            ];
            $LogstashRedis->lpush('logstash', json_encode($logstash_data));
            echo '===================>end' . PHP_EOL;
            return true;
        } catch (\Exception $exception) {
            Log::error('mlogic error=>' . $exception->getMessage(), [$this->data]);
            echo 'mlogic error=>' . $exception->getMessage() . PHP_EOL;
            return false;
        }
    }
}
