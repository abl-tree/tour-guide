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
            'last_name' => 'Lamparas',
            'birthdate' => '1997-04-19',
            'gender_id' => '1'
        ], [
            'first_name' => 'Jane',
            'middle_name' => 'John',
            'last_name' => 'Doe',
            'birthdate' => '1997-01-11',
            'gender_id' => '2'
        ]);

        foreach ($data as $key => $value) {
            UserInfo::create($value);
        }
    }
}
