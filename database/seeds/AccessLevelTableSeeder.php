<?php

use Illuminate\Database\Seeder;
use App\Models\AccessLevel;

class AccessLevelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array([
            'code' => 'admin',
            'name' => 'Admin'
        ], [
            'code' => 'tg',
            'name' => 'Tour Guide'
        ]);

        foreach ($data as $key => $value) {
            AccessLevel::create($value);
        }
    }
}
