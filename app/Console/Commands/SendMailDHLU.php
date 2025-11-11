<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DHLU\OperatorLineUpController;

class SendMailDHLU extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendEmailDHLU:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Email DHLU';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $controller = new OperatorLineUpController();
        $result     = $controller->EmailNotification();

        $this->info($result);
    }
}
