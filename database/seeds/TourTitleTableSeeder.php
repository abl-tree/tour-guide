<?php

use Illuminate\Database\Seeder;
use App\Models\TourTitle;

class TourTitleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array([
            'title' => 'Colo SG AM',
            'time' => 'am'
        ], [
            'title' => 'Colo SG PM',
            'time' => 'pm'
        ], [
            'title' => 'VAT SG AM',
            'time' => 'am'
        ], [
            'title' => 'VAT SG PM',
            'time' => 'pm'
        ]);

        foreach ($data as $key => $value) {
            TourTitle::create($value);
        }
    }
}
