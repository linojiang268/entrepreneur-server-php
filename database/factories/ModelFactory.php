<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(\Entrepreneur\Models\User::class, function (Faker\Generator $faker) {
    return [
        'name'           => $faker->name,
        'mobile'         => '1' . $faker->numerify('##########'),
        'password'       => bcrypt(str_random(10)),
        'business'       => str_random(256),
        'status'         => 1,
        'salt'           => str_random(16),
        'remember_token' => str_random(10),
        'created_at'     => date('Y-m-d H:i:s'),
    ];
});

$factory->define(\Entrepreneur\Models\Requirement::class, function (Faker\Generator $faker) {
    return [
        'user_id'    => $faker->randomNumber,
        'title'      => str_random(50),
        'contacts'   => $faker->name,
        'mobile'     => '1' . $faker->numerify('##########'),
        'intro'      => str_random(256),
        'status'     => 0,
        'begin_time' => date('Y-m-d', strtotime('+2 day')),
        'end_time'   => date('Y-m-d', strtotime('+12 day')),
        'created_at'     => date('Y-m-d H:i:s'),
    ];
});

$factory->define(\Entrepreneur\Models\Application::class, function (Faker\Generator $faker) {
    return [
        'user_id'    => $faker->randomNumber,
        'req_id'    => $faker->randomNumber,
        'contacts'   => $faker->name,
        'mobile'     => '1' . $faker->numerify('##########'),
        'intro'      => str_random(256),
        'status'     => 0,
    ];
});

