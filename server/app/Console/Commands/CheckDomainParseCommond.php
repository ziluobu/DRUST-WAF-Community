<?php

namespace App\Console\Commands;

use App\Models\Web;
use Illuminate\Console\Command;

class CheckDomainParseCommond extends Command
{
    /*
     * 检测是否解析
     * */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:domain-parse';

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
        $this->info('开始检测是否解析');
        $webs = Web::get();
        foreach ($webs as $web) {
            $parse = 0;
            if (gethostbyname($web->web_name) == config('api.parse_ip')) {
                $parse = 1;
            }
            if ($web->is_parse != $parse) {
                $web->is_parse = $parse;
                $web->saveQuietly();
            }
        }
        $this->info('检测结束');
        return 0;
    }
}
