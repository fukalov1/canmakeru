<?php

namespace App\Console\Commands;

use App\Protokol;
use Illuminate\Console\Command;

class MoveProtokols extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:move-protokols';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Перенос протоколов в нулевые акты';
    protected $protokol;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Protokol $protokol)
    {
        $this->protokol = $protokol;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->protokol;
    }
}
