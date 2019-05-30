<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array([
            'username' => 'admin',
            'user_info_id' => 1,
            'email' => 'admin@gmail.com',
            'password' => '12341234',
        ], [
            'username' => 'tour',
            'user_info_id' => 2,
            'email' => 'tour@gmail.com',
            'password' => '12341234',
        ]);

        foreach ($data as $key => $value) {
            User::create($value);
        }
    }
}
