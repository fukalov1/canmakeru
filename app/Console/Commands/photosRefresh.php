<?php

namespace App\Console\Commands;

use App\Protokol;
use Illuminate\Console\Command;

class photosRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photos:refresh {start=1 : Start process record} {offset=100 : Limit records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление местоположения фото в соотвествии с записями в бд';

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
        $start = $this->argument('start');
        $offset = $this->argument('offset');
        $this->protokol->refreshPhotos($start,$offset);
    }
}
