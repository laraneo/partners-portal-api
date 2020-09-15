<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Menu;
use Faker\Generator as Faker;

$factory->define(App\Menu::class, function (Faker\Generator $faker) {
    $name = $faker->name;
    $menus = App\Menu::all();
    return [
        'name' => $name,
        'slug' => str_slug($name),
        'parent' => (count($menus) > 0) ? $faker->randomElement($menus->pluck('id')->toArray()) : 0,
        'order' => 0
    ];
});
