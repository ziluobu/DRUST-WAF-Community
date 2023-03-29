<?php

namespace App\Http\Controllers\Api;

use App\Models\Web;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\CardinalityAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\SumAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\TopHitsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\ValueCountAggregation;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Search;

class IndexController extends BaseApiController
{
    // 首页
    public function home()
    {

    }

    public function visitAttackOverView(Request $request)
    {
        $date      = $request->input('date', date('Y-m-d'));
        $beginTime = Carbon::parse($date)->toIso8601String();
        if ($date == date('Y-m-d')) {
            $endTime = Carbon::parse(date('Y-m-d H:i:s'))->toIso8601String();
        } else {
            $endTime = Carbon::parse(date('Y-m-d', strtotime($date) + 86400))->toIso8601String();
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

        $sumAggregation = new SumAggregation('body_bytes_sent', 'body_bytes_sent');
        $visitSearch->addAggregation($sumAggregation);

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


        $attackDocs = $client->search([
            'index' => 'dxwaflog*',
            'body'  => $attackSearch->toArray(),
        ]);
        $visitDocs  = $client->search([
            'index' => 'apachelog*',
            'body'  => $visitSearch->toArray(),
        ]);

        $attack = [];
        // dd($attackDocs['aggregations']['groupDate']['buckets']);
        foreach ($attackDocs['aggregations']['groupDate']['buckets'] as $key => $value) {
            $attack[$key]['date'] = date('Y-m-d H:i', $value['key'] / 1000);
            $attack[$key]['num']  = $value['doc_count'];
        }
        $visit = [];
        foreach ($visitDocs['aggregations']['groupDate']['buckets'] as $key => $value) {
            $visit[$key]['date'] = date('Y-m-d H:i', $value['key'] / 1000);
            $visit[$key]['num']  = $value['doc_count'];
        }
        $visitFlowTotal = $visitDocs['aggregations']['body_bytes_sent']['value'] ?? 0;

        $data = [
            'visitTotal'     => $visitDocs['aggregations']['count_num']['value'] ?? 0,
            'visitFlowTotal' => formatBytes($visitFlowTotal),
            'attackTotal'    => $attackDocs['aggregations']['count_num']['value'] ?? 0,
            'attackIpCount'  => $attackDocs['aggregations']['attackIpNum']['value'] ?? 0,
            'attack'         => $attack,
            'visit'          => $visit
        ];
        return $this->success($data);
    }

    public function wafWarnPolicyRank(Request $request)
    {
        $date      = $request->input('date', date('Y-m-d'));
        $beginTime = Carbon::parse($date)->toIso8601String();
        $endTime   = Carbon::parse(date('Y-m-d', strtotime($date) + 86400))->toIso8601String();

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
                $attackType[$key]['total'] = $value['doc_count'];
                $allTotal                  += $value['doc_count'];
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
                    $attackHost[$key]['total'] = $value['doc_count'];
                    $allTotal                  += $value['doc_count'];
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
                    // $region                  = $value['top1']['hits']['hits'][0]['_source']['geoip']['region_name'] ?? '';
                    $attackIp[$key]['name']  = $value['key'] ?: '其他';
                    $attackIp[$key]['total'] = $value['doc_count'];
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
                        'total'    => $value,
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
                    $attackRegion[$key]['total'] = $value['doc_count'];
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
        ]);
    }


}
