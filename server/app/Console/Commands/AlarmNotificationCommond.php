<?php

namespace App\Console\Commands;

use App\Mail\AlarmNotificationMail;
use App\Models\Group;
use App\Models\Web;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use ONGR\ElasticsearchDSL\Aggregation\Metric\ValueCountAggregation;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Search;

class AlarmNotificationCommond extends Command
{
    /*
     * 发送告警邮件
     * */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:alarm {--period=30} {--attack=50}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $period    = $this->option('period');
            $attack    = $this->option('attack');
            $beginTime = Carbon::now()->toIso8601String();
            $endTime   = Carbon::parse("-$period minutes")->toIso8601String();
            $groupIds  = Group::pluck('id')->toArray();
            foreach ($groupIds as $groupId) {
                $bool         = new BoolQuery();
                $attackSearch = new Search();
                $client       = ClientBuilder::create()->setHosts(config('es.connections.default.servers'))->build();

                $web_names = Web::where('group_id', $groupId)->pluck('web_name')->toArray();
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
                        'gte' => $beginTime,
                        'lt'  => $endTime,
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

                $attackDocs = $client->search([
                    'index' => 'dxwaflog*',
                    'body'  => $attackSearch->toArray(),
                ]);

                $attackNum = $attackDocs['aggregations']['count_num']['value'] ?? 0;
                if ($attackNum > $attack) {
                    //发邮件
                    $message = (new AlarmNotificationMail())->onQueue('alarm');
                    Mail::to('')->queue($message);
                } else {
                    $this->info('no attack');
                }
                sleep(1);
            }
        } catch (\Exception $exception) {
            Log::error('command:alarm error=>' . $exception->getMessage());
        }

        return 0;
    }
}
