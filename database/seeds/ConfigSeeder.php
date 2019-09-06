<?php

use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin_config')->insert([
            'name' => 'YANDEX_CLIENT_ID',
            'value' => '1297b4a36010478c83b0a9867b293043',
            'description' => 'YANDEX_CLIENT_ID',
        ]);
        DB::table('admin_config')->insert([
            'name' => 'YANDEX_PASS',
            'value' => 'af9b6dd125e94a97acbd86d13d2ac863',
            'description' => 'YANDEX_PASS',
        ]);
        DB::table('admin_config')->insert([
            'name' => 'YANDEX_TOKEN',
            'value' => '',
            'description' => 'YANDEX_TOKEN',
        ]);
    }
}
