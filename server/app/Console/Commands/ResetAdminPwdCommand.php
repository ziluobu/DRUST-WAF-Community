<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;

class ResetAdminPwdCommand extends Command
{

    /*
     * 重置超级管理员密码
     * */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:reset-adminPwd';

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
            $password        = \Str::random(10) . '@';
            $Admin           = Admin::withoutGlobalScopes()->find(1);
            $Admin->password = \Hash::make($password);
            $Admin->save();

            $this->info('Password=>' . $password);
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
