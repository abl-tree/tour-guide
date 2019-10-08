<?php

use Illuminate\Database\Seeder;
use App\Models\TourType;

class TourTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array([
            'code' => 'small',
            'name' => 'Small Group Tour'
        ], [
            'code' => 'private',
            'name' => 'Private Tour'
        ]);

        foreach ($data as $key => $value) {
            TourType::create($value);
        }
    }
}
