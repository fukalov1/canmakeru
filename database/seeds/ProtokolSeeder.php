<?php

use App\Act;
use App\Protokol;
use Illuminate\Database\Seeder;

class ProtokolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $protokols = Protokol::all();
        $acts = new Act();
        foreach ($protokols as $protokol) {
            $act = $acts->where('customer_id', $protokol->customer_id)->first();
            Protokol::find($protokol->id)->update(['act_id'=>$act->id]);
        }
    }
}
