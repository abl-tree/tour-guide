<?php

use Illuminate\Database\Seeder;
use App\Models\ParticipantType;

class ParticipantTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array([
            'code' => 'child',
            'name' => 'Children'
        ], [
            'code' => 'adult',
            'name' => 'Adult'
        ]);

        foreach ($data as $key => $value) {
            ParticipantType::create($value);
        }
    }
}
