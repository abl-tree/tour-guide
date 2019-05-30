<?php

use Illuminate\Database\Seeder;
use App\Models\UserAccessLevel;

class UserAccessLevelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array([
            'user_id' => 1,
            'access_level_id' => 1
        ], [
            'user_id' => 2,
            'access_level_id' => 2
        ]);

        foreach ($data as $key => $value) {
            UserAccessLevel::create($value);
        }
    }
}
