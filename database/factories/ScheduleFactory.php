<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Schedule;
use Faker\Generator as Faker;

$factory->define(Schedule::class, function (Faker $faker) {
    return [
        'user_id' => function() {
            return factory(App\User::class)->create()->id;
        },
        'available_at' => '2019-05-31',
        'shift' => 'Evening'
    ];
});
