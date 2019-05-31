<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\UserInfo;
use Faker\Generator as Faker;

$factory->define(UserInfo::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstNameFemale,
        'last_name' => $faker->lastName,
        'birthdate' => "1990-12-12",
        'gender_id' => 2,
    ];
});
