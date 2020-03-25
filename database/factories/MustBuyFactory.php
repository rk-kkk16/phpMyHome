<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(App\Models\Mustbuy::class, function (Faker $faker) {
    return [
        'item_name' => $faker->name,
        'quantity' => $faker->numberBetween(1,10),
        'level' => $faker->numberBetween(1,5),
        'memo' => $faker->realText($faker->numberBetween(20,60)),
        'state' => $faker->randomElement(['yet', 'done']),
        'create_user_id' => $faker->randomElement([2, 3]),
    ];
});
