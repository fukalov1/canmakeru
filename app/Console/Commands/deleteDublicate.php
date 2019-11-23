<?php

namespace App\Console\Commands;

use App\Protokol;
use Illuminate\Console\Command;

class deleteDublicate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
        protected $signature = 'protokol:delete_dublicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $protokol;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Protokol $protokol)
    {
        parent::__construct();
        $this->protokol = $protokol;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->protokol->deleteDublicates();
    }
}
