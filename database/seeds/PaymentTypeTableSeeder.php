<?php

use Illuminate\Database\Seeder;
use App\Models\PaymentType;

class PaymentTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array([
            'code' => 'cash',
            'name' => 'Cash'
        ], [
            'code' => 'paypal',
            'name' => 'Paypal'
        ], [
            'code' => 'invoice',
            'name' => 'Invoice'
        ], [
            'code' => 'payoneer',
            'name' => 'Payoneer'
        ]);

        foreach ($data as $key => $value) {
            PaymentType::create($value);
        }
    }
}
