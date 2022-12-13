<?php

namespace App\Http\Extensions;


use App\Models\MonthReport;
use App\Models\RuleType;
use App\Models\Web;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\ValueCountAggregation;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Search;
use PhpOffice\PhpWord\Element\Chart;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\TemplateProcessor;

class CreateReport
{

    /*
     * 生成报告
     * type 1 月报 2自定义报告
     * */

    protected $company;
    protected $reportName;
    protected $begin_time;
    protected $end_time;
    protected $web_ids;
    // protected $group_id;
    protected $template_path;
    protected $title;

    public function __construct($company, $reportName, $begin_time, $end_time, $template_path, array $web_ids, $title)
    {
        $this->company       = $company;
        $this->reportName    = $reportName;
        $this->begin_time    = $begin_time;
        $this->end_time      = $end_time;
        $this->web_ids       = $web_ids;
        $this->title         = $title;
        $this->template_path = $template_path;
    }


    public function run()
    {
        $geneDate = date('Ymd');
        // $month         = Carbon::parse('-1 months');
        $dslStartTime = Carbon::parse($this->begin_time)->toIso8601String();
        $dslEndTime   = Carbon::parse($this->end_time)->toIso8601String();
        $dateRange    = $this->begin_time . ' ~ ' . $this->end_time;
        $createTime   = Carbon::now()->format('Y年m月d日');
        //实例化 phpword 类
        // $PHPWord = new PhpWord();
        //指定事先制作好的模板文件路径
        $templateProcessor = new TemplateProcessor($this->template_path);

        //渲染数据
        $templateProcessor->setValue('company', $this->company);
        $templateProcessor->setValue('title', $this->title);
        $templateProcessor->setValue('dateRange', $dateRange);
        $templateProcessor->setValue('createTime', $createTime);
        $templateProcessor->setValue('startMonth', $this->begin_time);
        $templateProcessor->setValue('endMonth', $this->end_time);
        $webs = Web::whereIn('id', $this->web_ids)->groupBy('web_name')->get()->toArray();
        $templateProcessor->setValue('host', $webs[0]['web_name']);
        $templateProcessor->setValue('hostNum', count($webs));
        $parseNum = count(array_filter($webs, function ($v) {
            return $v['is_parse'] == 1;
        }));
        $templateProcessor->setValue('parseNum', $parseNum);
        $templateProcessor->setValue('noParse', count($webs) - $parseNum);

        $bool         = new BoolQuery();
        $attackSearch = new Search();
        $client       = ClientBuilder::create()->setHosts(config('es.connections.default.servers'))->build();

        $web_names = array_column($webs, 'web_name');
        //筛选所属域名
        $termsQuery = new TermsQuery(
            'Hostname.keyword',
            $web_names
        );
        $bool->add($termsQuery, BoolQuery::FILTER);
        $attackSearch->setSize(0);
        //筛选时间段
        $rangeQuery = new RangeQuery(
            '@timestamp',
            [
                'gte' => $dslStartTime,
                'lt'  => $dslEndTime,
            ]
        );
        $bool->add($rangeQuery, BoolQuery::FILTER);
        // 筛选规则id
        $termQueryForUser = new TermQuery("type_id", 0);
        $bool->add($termQueryForUser, BoolQuery::MUST_NOT);
        $attackSearch->addQuery($bool);
        // 求和
        $valueCountAggregation = new ValueCountAggregation('count_num', '_id');
        $attackSearch->addAggregation($valueCountAggregation);

        //每日攻击趋势
        $dateHistogramAggregation = new DateHistogramAggregation('groupDate', '@timestamp', 'day');
        $dateHistogramAggregation->addParameter('format', 'yyyy-MM-dd');
        $dateHistogramAggregation->addParameter('time_zone', '+08:00');
        $dateHistogramAggregation->addParameter('min_doc_count', 0);
        $dateHistogramAggregation->addParameter('extended_bounds', [
            "min" => strtotime($dslStartTime) * 1000,
            "max" => strtotime($dslEndTime) * 1000
        ]);
        $attackSearch->addAggregation($dateHistogramAggregation);

        $termsAggregation = new TermsAggregation('hostname_genres', 'Hostname.keyword');
        $termsAggregation->addParameter('size', count($webs));
        $typeAggregation = new TermsAggregation('type_genres', 'type_id');
        $typeAggregation->addParameter('size', 5);
        $termsAggregation->addAggregation($typeAggregation);
        $attackSearch->addAggregation($termsAggregation);

        // 3.1 攻击类型详情分析
        $typeAggregation = new TermsAggregation('types_genres', 'type_id');
        $hostAggregation = new TermsAggregation('host_genres', 'Hostname.keyword');
        $urlAggregation  = new TermsAggregation('url_genres', 'Url.keyword');
        $urlAggregation->addParameter('size', 5);
        $hostAggregation->addAggregation($urlAggregation);
        $typeAggregation->addAggregation($hostAggregation);
        $attackSearch->addAggregation($typeAggregation);

        // 3.2攻击源TOP 10分析
        $ipAggregation = new TermsAggregation('ip_genres', 'ipInfo.ip.keyword');
        $ipAggregation->addParameter('size', 10);
        $hostAggregation = new TermsAggregation('host_genres', 'Hostname.keyword');
        $hostAggregation->addParameter('size', 1);
        $hostAggregation = new TermsAggregation('host_genres', 'Hostname.keyword');
        $hostAggregation->addParameter('size', 1);
        $typeAggregation = new TermsAggregation('types_genres', 'type_id');
        $typeAggregation->addParameter('size', 1);
        $hostAggregation->addAggregation($typeAggregation);
        $ipAggregation->addAggregation($hostAggregation);
        $attackSearch->addAggregation($ipAggregation);

        // 3.3.攻击区域详情分析
        $regionAggregation = new TermsAggregation('region_genres', 'ipInfo.state_name.keyword');
        $ipAggregation     = new TermsAggregation('ip_genres', 'ipInfo.ip.keyword');
        $ipAggregation->addParameter('size', 5);
        $regionAggregation->addAggregation($ipAggregation);
        $attackSearch->addAggregation($regionAggregation);

        $attackDocs = $client->search([
            'index' => 'dxwaflog*',
            'body'  => $attackSearch->toArray(),
        ]);
        // dd($attackDocs);
        $attackNum = $attackDocs['aggregations']['count_num']['value'] ?? 0;
        $templateProcessor->setValue('attackNum', $attackNum);

        $categories = [];
        $series1    = [];
        foreach ($attackDocs['aggregations']['groupDate']['buckets'] as $key => $value) {
            $attack[$key]['date'] = date('Y-m-d H:i', $value['key'] / 1000);
            $attack[$key]['num']  = $value['doc_count'];
            $categories[]         = date('m月d日', $value['key'] / 1000);
            $series1[]            = $value['doc_count'];
        }
        $chart = new Chart('line', $categories, $series1, [], '网站群受攻击趋势');

        $dataLabel = [
            'showCatName' => false,
        ];
        $chart->getStyle()
            ->setTitle('网站群受攻击趋势')
            ->setShowAxisLabels()
            ->setWidth(Converter::inchToEmu(5.8))
            ->setHeight(Converter::inchToEmu(4))
            ->setShowGridY()
            ->setDataLabelOptions($dataLabel);
        $templateProcessor->setChart("line1", $chart);

        $allTotal   = 0;
        $attackHost = [];
        $ruleTypes  = RuleType::get()->toArray();
        $ruleTypes  = array_column($ruleTypes, null, 'id');
        foreach ($attackDocs['aggregations']['hostname_genres']['buckets'] as $key => $value) {
            if ($value) {
                $attackHost[$key]['name']  = $value['key'] ?: '其他';
                $attackHost[$key]['total'] = $value['doc_count'];
                $allTotal                  += $value['doc_count'];
                $typeStr                   = "";
                if (isset($value['type_genres']['buckets'])) {
                    foreach ($value['type_genres']['buckets'] as $k => $v) {
                        $typeStr .= ($ruleTypes[$v['key']]['name'] ?? '-') . "（{$v['doc_count']}），";
                        // $types[$k]['name']  = $ruleTypes[$v['key']] ?? '-';
                        // $types[$k]['total'] = $v['doc_count'];
                    }
                    $typeStr = trim($typeStr, '，');
                }
                $attackHost[$key]['types'] = $typeStr;

            }
        }

        $attackHost2 = $attackHost;

        if (!$attackHost2) {
            $top2Web = array_slice($webs, 0, 2);
            foreach ($top2Web as $key => $web) {
                $attackHost2[$key]['name']  = $web['web_name'];
                $attackHost2[$key]['total'] = 0;
                $allTotal                   += 0;
            }
        }
        $attackTop2 = '';
        foreach ($attackHost2 as $key => $value) {
            if ($key < 2) {
                $bili       = sprintf('%.2f', $allTotal != 0 ? ($value['total'] * 100 / $allTotal) : 0);
                $attackTop2 .= "（{$value['name']}，攻击量{$value['total']}次，占比$bili%）、";
            }
        }

        $attackTop2 = trim($attackTop2, '、') . '等站点';
        $templateProcessor->setValue('attackTop2', $attackTop2);
        $chart     = new Chart('pie', array_column($attackHost2, 'name'), array_column($attackHost2, 'total'), [], '业务系统遭受攻击分布');
        $dataLabel = [
            'showVal'     => false, // value
            'showCatName' => false, // category name
            'showPercent' => true,
        ];
        $chart->getStyle()
            ->setTitle('业务系统遭受攻击分布')
            ->setWidth(Converter::inchToEmu(5.8))
            ->setHeight(Converter::inchToEmu(3.5))
            ->setShowLegend(true)
            ->setDataLabelOptions($dataLabel);

        $templateProcessor->setChart("pie1", $chart);

        $templateProcessor->cloneRow('allSort', count($attackHost));

        foreach ($attackHost as $key => $value) {
            $templateProcessor->setValue("allSort#" . ($key + 1), $key + 1);
            $templateProcessor->setValue("allHosts#" . ($key + 1), $value['name']);
            $templateProcessor->setValue("allType#" . ($key + 1), $value['types']);
            $templateProcessor->setValue("allNum#" . ($key + 1), $value['total']);
            $bili = sprintf('%.2f', $allTotal != 0 ? ($value['total'] * 100 / $allTotal) : 0);
            $templateProcessor->setValue("allScale#" . ($key + 1), $bili . '%');
        }

        // 攻击类型详情分析
        // Clone Block
        $types_genres = $attackDocs['aggregations']['types_genres']['buckets'];
        $replacements = [];
        foreach ($types_genres as $key => $value) {
            $replacement['type_block_sort'] = $key + 1;
            $replacement['type_name']       = $ruleTypes[$value['key']]['name'] ?? '-';
            $replacement['type_describe']   = $ruleTypes[$value['key']]['describe'] ?? '-';
            $replacement['type_sort']       = '${type_sort_' . $key + 1 . '}';
            $replacement['type_host']       = '${type_host_' . $key + 1 . '}';
            $replacement['type_urls']       = '${type_urls_' . $key + 1 . '}';
            $replacement['type_num']        = '${type_num_' . $key + 1 . '}';
            $replacements[$key]             = $replacement;
        }
        $templateProcessor->cloneBlock('TYPE_BLOCK', count($types_genres), true, false, $replacements);

        // Clone Table Row
        foreach ($types_genres as $key => $value) {
            $replacements = [];
            $sort         = $key + 1;
            foreach ($value['host_genres']['buckets'] as $key1 => $value1) {
                $sort1      = $key1 + 1;
                $type_urls  = '';
                $type_count = 0;
                foreach ($value1['url_genres']['buckets'] as $key3 => $value3) {
                    $type_urls  .= $value3['key'] . "【{$value3['doc_count']}次】、";
                    $type_count += $value3['doc_count'];
                }
                $replacements[$key1]["type_sort_{$sort}"] = $sort1;
                $replacements[$key1]["type_host_{$sort}"] = $value1['key'];
                $replacements[$key1]["type_urls_{$sort}"] = $type_urls;
                $replacements[$key1]["type_num_{$sort}"]  = $type_count;
            }
            $templateProcessor->cloneRowAndSetValues("type_sort_{$sort}", $replacements);
        }

        // 3.2.攻击源TOP 10分析

        $ipTop3tr     = '';
        $replacements = [];
        foreach ($attackDocs['aggregations']['ip_genres']['buckets'] as $key => $value) {
            $replacements[$key]['iptop_sort']   = $key + 1;
            $replacements[$key]['iptop_ip']     = $value['key'] ?? '-';
            $ipInfo                             = getgeoIpInfo($value['key']);
            $replacements[$key]['iptop_region'] = $ipInfo['country'] . $ipInfo['city'];
            if (isset($value['host_genres']['buckets'][0]['key'])) {
                $iptop_host = $value['host_genres']['buckets'][0]['key'] . "（{$value['host_genres']['buckets'][0]['doc_count']}次）";

            } else {
                $iptop_host = '-';
            }
            $replacements[$key]['iptop_host'] = $iptop_host;
            $type_id                          = $value['host_genres']['buckets'][0]['types_genres']['buckets'][0]['key'] ?? 0;
            $replacements[$key]['iptop_type'] = $ruleTypes[$type_id]['name'] ?? '-';

            $hostName = $value['host_genres']['buckets'][0]['key'] ?? '-';
            $hostNum  = $value['host_genres']['buckets'][0]['doc_count'] ?? '-';
            if ($key < 3) {
                $ipTop3tr .= "{$replacements[$key]['iptop_region']}的IP为{$replacements[$key]['iptop_ip']}针对($hostName)发起累计{$hostNum}次以{$replacements[$key]['iptop_type']}等类型的Web攻击；";
            }
        }
        $ipTop3tr = rtrim($ipTop3tr, '；');
        $templateProcessor->setValue("ipTop3Str", $ipTop3tr ?: '-');
        $templateProcessor->cloneRowAndSetValues("iptop_sort", $replacements);

        // 3.3.攻击区域详情分析
        $replacements  = [];
        $allTotal      = 0;
        $regionBuckets = $attackDocs['aggregations']['region_genres']['buckets'];
        if ($regionBuckets) {
            $allTotal = array_sum(array_column($regionBuckets, 'doc_count'));
        }
        foreach ($regionBuckets as $key => $value) {
            $replacements[$key]['regiontop_sort']   = $key + 1;
            $replacements[$key]['regiontop_region'] = $value['key'] ?? '-';
            $regiontop_ip                           = '';
            foreach ($value['ip_genres']['buckets'] as $value2) {
                $regiontop_ip .= $value2['key'] . "（{$value2['doc_count']}次）" . PHP_EOL;
            }
            $replacements[$key]['regiontop_ip'] = $regiontop_ip;

            $replacements[$key]['regiontop_num']   = $value['doc_count'];
            $bili                                  = sprintf('%.2f', $allTotal != 0 ? ($value['doc_count'] * 100 / $allTotal) : 0);
            $replacements[$key]['regiontop_scale'] = $bili . '%';
        }
        $templateProcessor->cloneRowAndSetValues("regiontop_sort", $replacements);

        $chart     = new Chart('pie', array_column($replacements, 'regiontop_region'), array_column($replacements, 'regiontop_num'), [], '攻击区域分布情况');
        $dataLabel = [
            'showVal'     => false, // value
            'showCatName' => false, // category name
            'showPercent' => true,
        ];
        $chart->getStyle()
            ->setTitle('攻击区域分布情况')
            ->setWidth(Converter::inchToEmu(5.8))
            ->setHeight(Converter::inchToEmu(3.5))
            ->setShowLegend(true)
            ->setDataLabelOptions($dataLabel);

        $templateProcessor->setChart("pie2", $chart);

        $path = storage_path('app/report/' . $geneDate);
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $saveName = \Str::uuid() . '.docx';
        $savePath = $path . '/' . $saveName;
        $templateProcessor->saveAs($savePath);

        $wordPath = $geneDate . '/' . $saveName;
        return $wordPath;
    }
}
