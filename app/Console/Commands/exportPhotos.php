<?php

namespace App\Console\Commands;

use App\Protokol;
use Illuminate\Console\Command;

class exportPhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yandex:export  {file : обязательный параметр имя файла}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Экспорт файлов на Yandex.Disk';
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

        $this->info($this->description);
        $file = $this->argument('file');
        if($file) {
            $result = $this->protokol->uploadFile($file);
            if ($result) {
                echo "Upload files to Yandex.disk successfully\n";
            }
        }
    }
}
