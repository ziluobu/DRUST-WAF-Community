<?php

namespace App\Http\Controllers\Api;

use App\Models\Web;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\CardinalityAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\TopHitsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\ValueCountAggregation;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Search;

/**
 * 大屏
 */
class ScreenController extends BaseApiController
{


    public function visitAttackOverView(Request $request)
    {
        $beginTime = Carbon::now()->startOfDay()->toIso8601String();
        $endTime   = Carbon::now()->toIso8601String();
        $type      = $request->input('date', 0);
        $hour      = date('G');
        switch ($type) {
            case 1:
                if ($hour - 12 > 0) {
                    $beginTime = Carbon::parse('-12 hours')->toIso8601String();
                }
                break;
            case 2:
                if ($hour - 1 > 0) {
                    $beginTime = Carbon::parse('-1 hours')->toIso8601String();
                }
                break;
        }
        $bool         = new BoolQuery();
        $vistiBool    = new BoolQuery();
        $attackSearch = new Search();
        $visitSearch  = new Search();
        $client       = ClientBuilder::create()->setHosts(config('es.connections.default.servers'))->build();

        $group_id = $request->input('user_group_id');
        if ($group_id > 0
            // && request()->input('user_id') != 1
        ) {
            $web_names  = Web::where('group_id', $group_id)->pluck('web_name')->toArray();
            $termsQuery = new TermsQuery(
                'Hostname.keyword',
                $web_names
            );
            $bool->add($termsQuery, BoolQuery::FILTER);
            $vistiBool->add($termsQuery, BoolQuery::FILTER);
        }

        $attackSearch->setSize(0);
        $visitSearch->setSize(0);
        $rangeQuery = new RangeQuery(
            '@timestamp',
            [
                'gte' => $beginTime,
                'lt'  => $endTime,
            ]
        );
        $vistiBool->add($rangeQuery, BoolQuery::FILTER);
        $bool->add($rangeQuery, BoolQuery::FILTER);

        $visitSearch->addQuery($vistiBool);

        $termQueryForUser = new TermQuery("type_id", 0);
        $bool->add($termQueryForUser, BoolQuery::MUST_NOT);

        $attackSearch->addQuery($bool);

        $valueCountAggregation = new ValueCountAggregation('count_num', '_id');
        $attackSearch->addAggregation($valueCountAggregation);
        $visitSearch->addAggregation($valueCountAggregation);

        $cardinalityAggregation = new CardinalityAggregation('attackIpNum');
        $cardinalityAggregation->setField('attack_ip.keyword');
        $attackSearch->addAggregation($cardinalityAggregation);

        $dateHistogramAggregation = new DateHistogramAggregation('groupDate', '@timestamp', '10m');
        $dateHistogramAggregation->addParameter('format', 'yyyy-MM-dd HH');
        $dateHistogramAggregation->addParameter('time_zone', '+08:00');
        $dateHistogramAggregation->addParameter('min_doc_count', 0);
        $dateHistogramAggregation->addParameter('extended_bounds', [
            "min" => strtotime($beginTime) * 1000,
            "max" => strtotime($endTime) * 1000 - 1
        ]);
        $attackSearch->addAggregation($dateHistogramAggregation);
        $visitSearch->addAggregation($dateHistogramAggregation);

        // dd($attackSearch->toArray());
        $attackDocs = $client->search([
            'index' => 'dxwaflog*',
            'body'  => $attackSearch->toArray(),
        ]);
        $visitDocs  = $client->search([
            'index' => 'apachelog*',
            'body'  => $visitSearch->toArray(),
        ]);

        $attack = [];
        foreach ($attackDocs['aggregations']['groupDate']['buckets'] as $key => $value) {
            // $k                    = date('Y-m-d H:i', $value['key'] / 1000);
            // $attack[$k]           = $value['doc_count'];
            $attack[$key]['date'] = date('Y-m-d H:i', $value['key'] / 1000);
            $attack[$key]['num']  = $value['doc_count'];
        }
        $visit = [];
        foreach ($visitDocs['aggregations']['groupDate']['buckets'] as $key => $value) {
            // $k                   = date('Y-m-d H:i', $value['key'] / 1000);
            // $visit[$k]           = $value['doc_count'];
            $visit[$key]['date'] = date('Y-m-d H:i', $value['key'] / 1000);
            $visit[$key]['num']  = $value['doc_count'];
        }
        $data = [
            'visitTotal'    => $visitDocs['aggregations']['count_num']['value'] ?? 0,
            'attackTotal'   => $attackDocs['aggregations']['count_num']['value'] ?? 0,
            'attackIpCount' => $attackDocs['aggregations']['attackIpNum']['value'] ?? 0,
            'trappCount'    => 0,
            'attack'        => $attack,
            'visit'         => $visit
        ];
        return $this->success($data);
    }

    public function realTimeAttackLog(Request $request)
    {
        $beginTime = Carbon::now()->startOfDay()->toIso8601String();
        $endTime   = Carbon::now()->toIso8601String();

        $type = $request->input('date', 0);
        $hour = date('G');
        switch ($type) {
            case 1:
                if ($hour - 12 > 0) {
                    $beginTime = Carbon::parse('-12 hours')->toIso8601String();
                }
                break;
            case 2:
                if ($hour - 1 > 0) {
                    $beginTime = Carbon::parse('-1 hours')->toIso8601String();
                }
                break;
        }

        $query    = app('es')->index('dxwaflog*')
            ->whereBetween('@timestamp', [$beginTime, $endTime])
            ->orderBy('@timestamp', 'desc')
            ->take(50)->skip(0)
            ->whereNot('type_id', 0)
            ->select("Hostname", "attack_ip", "@timestamp", "ipInfo.country", "ipInfo.city", "ipInfo.state_name");
        $group_id = $request->input('user_group_id');
        if ($group_id > 0
            // && request()->input('user_id') != 1
        ) {
            $web_names = Web::where('group_id', $group_id)->pluck('web_name')->toArray();
            $query->whereIn('Hostname.keyword', $web_names);
        }

        $response = $query->get()->toArray();
        $list     = [];
        foreach ($response as $k => $v) {
            $attackTime                = Carbon::parse($v['@timestamp'])->setTimezone(config('app.timezone'))->toTimeString();
            $list[$k]['attackTime']    = $attackTime;
            $list[$k]['hostName']      = $v['Hostname'];
            $list[$k]['attackIp']      = $v['attack_ip'];
            $list[$k]['action']        = '允许';
            $list[$k]['attackName']    = '高危';
            $list[$k]['destAssetArea'] = '电视台';
            $list[$k]['destArea']      = '郑州';
            $list[$k]['destCountry']   = '中国';
            $list[$k]['destIp']        = '127.0.0.1';
            $list[$k]['destIpIsOutIn'] = '0';
            $list[$k]['srcIpIsOutIn']  = '1';
            $list[$k]['destProvince']  = '河南';
            $list[$k]['occur_time']    = $attackTime;
            $list[$k]['srcAssetArea']  = '';
            $list[$k]['srcCity']       = str_replace(['市'], '', ($v['ipInfo']['city'] ?? ''));
            $list[$k]['srcCountry']    = $v['ipInfo']['country'] ?? '';
            $list[$k]['scrIp']         = $v['attack_ip'] ?? '';
            $list[$k]['srcProvince']   = str_replace(['省'], '', ($v['ipInfo']['state_name'] ?? ''));
        }
        /*        $ips = [
                    '117.159.27.58',             //北京
                    '182.119.216.216',           //合肥
                    '218.18.109.228',            //广东
                    '8.39.131.24',               //美国
                    '20.108.186.17',             //英国
                    '203.198.23.69',             //香港
                    '60.246.49.9',               //澳门
                    '114.44.227.87',             //台湾
                    '61.164.153.211',            //浙江
                    '61.157.248.35',             //四川
                    '60.13.144.23',              //新疆
                    '222.221.6.75',              //山东
                    '218.4.65.171',              //江苏
                    '59.45.243.14',              //辽宁
                    '218.76.73.33',              //湖南
                    '59.175.130.111',            //湖北
                    '222.161.137.205',           //吉林
                    '221.7.151.250',             //广西
                    '182.240.229.28',            //云南
                    '1.58.152.25',               //黑龙江
                    '27.184.96.25',              //河北
                    '58.18.169.19',              //内蒙
                    '61.189.191.154',            //贵州
                    '61.178.8.138',              //甘肃
                    '220.171.1.180',             //新疆
                ];

                $areas  = json_decode(file_get_contents(base_path('template/area.json')), true);
                $fakers = [];
                foreach ($areas as $k => $v) {
                    $fakers[$k]['srcProvince'] = str_replace(['省'], '', $v['name']);
                    $fakers[$k]['srcCountry']  = '中国';
                    $fakers[$k]['srcCity']     = str_replace(['市'], '', $v['children'][0]['name']);
                }
                shuffle($fakers);
                $fakers    = array_slice($fakers, 0, 20);
                $hostnames = array_column($response, 'Hostname');

                $new = [];
                foreach ($fakers as $i => $v) {
                    $num                      = mt_rand(0, 120);
                    $attackTime               = Carbon::parse("- $num minutes")->toTimeString();
                    $new[$i]['attackTime']    = $attackTime;
                    $new[$i]['hostName']      = $hostnames[array_rand($hostnames)];
                    $ip                       = $ips[$i];
                    $new[$i]['attackIp']      = $ip;
                    $new[$i]['action']        = '允许';
                    $new[$i]['attackName']    = '高危';
                    $new[$i]['destAssetArea'] = '电视台';
                    $new[$i]['destArea']      = '郑州';
                    $new[$i]['destCountry']   = '中国';
                    $new[$i]['destIp']        = '127.0.0.1';
                    $new[$i]['destIpIsOutIn'] = '0';
                    $new[$i]['srcIpIsOutIn']  = '1';
                    $new[$i]['destProvince']  = '河南';
                    $new[$i]['occur_time']    = $attackTime;
                    $new[$i]['srcAssetArea']  = '';
                    // $ipInfo                      = getgeoIpInfo($ip);
                    $new[$i]['srcCity']     = $v['srcCity'] ?? '';
                    $new[$i]['srcCountry']  = $v['srcCountry'] ?? '';
                    $new[$i]['scrIp']       = $ip;
                    $new[$i]['srcProvince'] = $v['srcProvince'] ?? '';
                }
                $guowai      = [
                    '8.39.131.24',               //美国
                    '20.108.186.17',             //英国
                ];
                $guowai_data = [];
                foreach ($guowai as $i => $v) {
                    $num                              = mt_rand(0, 120);
                    $attackTime                       = Carbon::parse("- $num minutes")->toTimeString();
                    $guowai_data[$i]['attackTime']    = $attackTime;
                    $guowai_data[$i]['hostName']      = $hostnames[array_rand($hostnames)];
                    $ip                               = $v;
                    $guowai_data[$i]['attackIp']      = $ip;
                    $guowai_data[$i]['action']        = '允许';
                    $guowai_data[$i]['attackName']    = '高危';
                    $guowai_data[$i]['destAssetArea'] = '电视台';
                    $guowai_data[$i]['destArea']      = '郑州';
                    $guowai_data[$i]['destCountry']   = '中国';
                    $guowai_data[$i]['destIp']        = '127.0.0.1';
                    $guowai_data[$i]['destIpIsOutIn'] = '0';
                    $guowai_data[$i]['srcIpIsOutIn']  = '1';
                    $guowai_data[$i]['destProvince']  = '河南';
                    $guowai_data[$i]['occur_time']    = $attackTime;
                    $guowai_data[$i]['srcAssetArea']  = '';
                    $ipInfo                           = getgeoIpInfo($ip);
                    $guowai_data[$i]['srcCity']       = $ipInfo['city'] ?? '';
                    $guowai_data[$i]['srcCountry']    = $ipInfo['country'] ?? '';
                    $guowai_data[$i]['scrIp']         = $ipInfo['ip'] ?? '';
                    $guowai_data[$i]['srcProvince']   = $ipInfo['state_name'] ?? '';
                }
                $new = array_merge($new, $guowai_data);*/
        return $this->success($list);
    }

    public function wafWarnPolicyRank(Request $request)
    {
        $beginTime = Carbon::now()->startOfDay()->toIso8601String();
        $endTime   = Carbon::now()->toIso8601String();

        $type = $request->input('date', 0);
        $hour = date('G');
        switch ($type) {
            case 1:
                if ($hour - 12 > 0) {
                    $beginTime = Carbon::parse('-12 hours')->toIso8601String();
                }
                break;
            case 2:
                if ($hour - 1 > 0) {
                    $beginTime = Carbon::parse('-1 hours')->toIso8601String();
                }
                break;
        }

        $attackSearch = new Search();
        $bool         = new BoolQuery();
        $client       = ClientBuilder::create()->setHosts(config('es.connections.default.servers'))->build();

        $group_id = $request->input('user_group_id');
        if ($group_id > 0
            // && request()->input('user_id') != 1
        ) {
            $web_names  = Web::where('group_id', $group_id)->pluck('web_name')->toArray();
            $termsQuery = new TermsQuery(
                'Hostname.keyword',
                $web_names
            );
            $bool->add($termsQuery, BoolQuery::FILTER);
        }

        $attackSearch->setSize(0);
        $rangeQuery = new RangeQuery(
            '@timestamp',
            [
                'gte' => $beginTime,
                'lt'  => $endTime,
            ]
        );
        $bool->add($rangeQuery, BoolQuery::FILTER);
        $attackSearch->addQuery($bool);

        $termQueryForUser = new TermQuery("type_id", 0);
        $bool->add($termQueryForUser, BoolQuery::MUST_NOT);

        $termsAggregation = new TermsAggregation('type_name_genres', 'type_name.keyword');
        $termsAggregation->addParameter('size', 5);
        $attackSearch->addAggregation($termsAggregation);

        $termsAggregation = new TermsAggregation('hostname_genres', 'Hostname.keyword');
        $termsAggregation->addParameter('size', 5);
        $attackSearch->addAggregation($termsAggregation);


        $termsAggregation = new TermsAggregation('attack_ip_genres', 'attack_ip.keyword');
        $termsAggregation->addParameter('size', 5);
        $attackSearch->addAggregation($termsAggregation);

        // $topHitsAggregation = new TopHitsAggregation('top1', 1);
        // $topHitsAggregation->addParameter('_source', ['attack_ip']);
        $termsAggregation = new TermsAggregation('region_name_genres', 'ipInfo.state_name.keyword');
        $termsAggregation->addParameter('size', 5);
        // $termsAggregation->addAggregation($topHitsAggregation);

        $attackSearch->addAggregation($termsAggregation);


        $attackDocs   = $client->search([
            'index' => 'dxwaflog*',
            'body'  => $attackSearch->toArray(),
        ]);
        $attackType   = [];
        $attackHost   = [];
        $attackIp     = [];
        $attackRegion = [];
        $allTotal     = 0;

        //攻击类型排行
        foreach ($attackDocs['aggregations']['type_name_genres']['buckets'] as $key => $value) {
            if ($value) {
                $attackType[$key]['name']  = $value['key'] ?: '其他';
                $attackType[$key]['value'] = $value['doc_count'];
                $allTotal                  += $value['doc_count'];
                // $attackType[$key]['allTotal'] = $allTotal;
            }
        }
        if ($attackType) {
            foreach ($attackType as $key => $value) {
                $attackType[$key]['allTotal'] = $allTotal;
            }
            //受攻击域名当日排行
            $allTotal = 0;
            foreach ($attackDocs['aggregations']['hostname_genres']['buckets'] as $key => $value) {
                if ($value) {
                    $attackHost[$key]['name']  = $value['key'] ?: '其他';
                    $attackHost[$key]['value'] = $value['doc_count'];
                    $allTotal                  += $value['doc_count'];
                    // $attackHost[$key]['allTotal'] = $allTotal;
                }
            }

            if ($attackHost) {
                foreach ($attackHost as $key => $value) {
                    $attackHost[$key]['allTotal'] = $allTotal;
                }
            }
            // 攻击源IP排行
            $allTotal = 0;
            foreach ($attackDocs['aggregations']['attack_ip_genres']['buckets'] as $key => $value) {
                if ($value) {
                    $attackIp[$key]['name']  = $value['key'] ?: '其他';
                    $attackIp[$key]['value'] = $value['doc_count'];
                    $allTotal                += $value['doc_count'];
                }
            }
            if ($attackIp) {
                foreach ($attackIp as $key => $value) {
                    $attackIp[$key]['allTotal'] = $allTotal;
                }
            }
            // 攻击区域分布情况
            /*$allTotal = 0;
            foreach ($attackDocs['aggregations']['region_name_genres']['buckets'] as $key => $value) {
                if ($value) {
                    $ip = $value['top1']['hits']['hits'][0]['_source']['attack_ip'] ?? '';
                    if ($ip) {
                        $ipInfo = getgeoIpInfo($ip);
                        $city   = $ipInfo['state_name'] ?: '其他';
                    } else {
                        $city = '其他';
                    }
                    $attackRegion[$city] = $attackRegion[$city] ?? 0;
                    $attackRegion[$city] += $value['doc_count'];
                }
            }
            arsort($attackRegion);
            $attackRegion = array_slice($attackRegion, 0, 5);
            if ($attackRegion) {
                $allTotal = array_sum($attackRegion);
                foreach ($attackRegion as $key => $value) {
                    $info              = [
                        'name'     => $key,
                        'value'    => $value,
                        'allTotal' => $allTotal,
                    ];
                    $attackRegionDis[] = $info;
                }
                // $attackRegionDis = array_slice($attackRegionDis, 0, 5);
            }*/
            $allTotal = 0;
            foreach ($attackDocs['aggregations']['region_name_genres']['buckets'] as $key => $value) {
                if ($value) {
                    $attackRegion[$key]['name']  = $value['key'] ?: '其他';
                    $attackRegion[$key]['value'] = $value['doc_count'];
                    $allTotal                    += $value['doc_count'];
                }
            }
            if ($attackRegion) {
                foreach ($attackRegion as $key => $value) {
                    $attackRegion[$key]['allTotal'] = $allTotal;
                }
            }

        }
        return $this->success([
            'typeRank'   => $attackType,
            'domainRank' => $attackHost,
            'ipRank'     => $attackIp,
            'regionRank' => $attackRegion,
            'trappRank'  => [
                ['name' => 'mysql', 'value' => '100', 'allTotal' => '200'],
                ['name' => 'redis', 'value' => '60', 'allTotal' => '200'],
                ['name' => 'ssh', 'value' => '20', 'allTotal' => '200'],
                ['name' => 'ftp', 'value' => '20', 'allTotal' => '200'],
            ],
        ]);
    }


}
