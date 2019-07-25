<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
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
            'password' => 'password',
            'accepted_at' => Carbon::now()
        ], [
            'username' => 'tour',
            'user_info_id' => 2,
            'email' => 'tour@gmail.com',
            'password' => 'password',
            'accepted_at' => Carbon::now()
        ]);

        foreach ($data as $key => $value) {
            User::create($value);
        }
    }
}
