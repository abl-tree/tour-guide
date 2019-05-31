<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Schedule;
use Faker\Generator as Faker;

$factory->define(Schedule::class, function (Faker $faker) {
    $shifts = array('Morning', 'Afternoon', 'Evening');
    
    return [
        'user_id' => function() {
            return factory(App\User::class)->create()->id;
        },
        'available_at' => $faker->dateTimeBetween('- 5 days', strtotime('2019-06-30')),
        'shift' => $shifts[array_rand($shifts)]
    ];
});
