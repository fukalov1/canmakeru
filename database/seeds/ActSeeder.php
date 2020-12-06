<?php

use App\Act;
use App\Customer;
use Illuminate\Database\Seeder;

class ActSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customers = Customer::all();
        $acts = new Act();
        foreach ($customers as $customer) {
            $acts->insert([
                'customer_id' => $customer->id,
                'number_act' => $customer->partner_code.'-20-0',
                'name' => 'Нулевой'
            ]);
        }
    }
}
