<?php

namespace App\Console\Commands;

use App\Admin\Controllers\TrancactionControler;
use Illuminate\Console\Command;

class TransactionStateCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:state_check';

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
        $transaction = new TrancactionControler();
        $transaction->updateStatus();
        return 0;
    }
}
