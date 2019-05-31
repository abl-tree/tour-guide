<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\UserAccessLevel;
use Faker\Generator as Faker;

$factory->define(UserAccessLevel::class, function (Faker $faker) {
    return [
        'user_id' => function() {
            return factory(App\User::class)->create()->id;
        },
        'access_level_id' => 2
    ];
});
