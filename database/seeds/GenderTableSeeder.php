<?php

use Illuminate\Database\Seeder;
use App\Models\Gender;

class GenderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array([
            'name' => 'Male'
        ], [
            'name' => 'Female'
        ]);

        foreach ($data as $key => $value) {
            Gender::create($value);
        }
    }
}
