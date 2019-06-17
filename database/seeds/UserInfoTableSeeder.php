<?php

use Illuminate\Database\Seeder;
use App\Models\UserInfo;

class UserInfoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array([
            'first_name' => 'Allen',
            'middle_name' => 'Beciera',
            'last_name' => 'Lamparas'
        ], [
            'first_name' => 'Jane',
            'middle_name' => 'John',
            'last_name' => 'Doe'
        ]);

        foreach ($data as $key => $value) {
            UserInfo::create($value);
        }
    }
}
