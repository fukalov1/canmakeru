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
    protected $signature = 'photos:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление местоположения фото в соотвесьтвии с записями в бд';

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
        $this->protokol->refreshPhotos();
    }
}
