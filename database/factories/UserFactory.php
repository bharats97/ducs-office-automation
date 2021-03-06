<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\College;
use App\Models\User;
use App\Types\Designation;
use App\Types\TeacherStatus;
use App\Types\UserCategory;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, static function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'category' => $faker->randomElement(UserCategory::values()),
        'email' => $faker->unique()->safeEmail,
        'phone' => $faker->unique()->regexify('[9876][0-9]{9}'),
        'address' => $faker->address,
        'college_id' => factory(College::class),
        'status' => function ($user) use ($faker) {
            return in_array($user['category'], [
                UserCategory::COLLEGE_TEACHER,
            ]) ? $faker->randomElement(TeacherStatus::values())
                : null;
        },
        'designation' => $faker->randomElement(Designation::values()),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ];
});

$factory->state(User::class, 'supervisor', [
    'category' => UserCategory::FACULTY_TEACHER,
    'is_supervisor' => true,
    'is_cosupervisor' => false,
]);

$factory->state(User::class, 'cosupervisor', [
    'category' => UserCategory::FACULTY_TEACHER,
    'is_supervisor' => false,
    'is_cosupervisor' => true,
]);

$factory->state(User::class, 'external', [
    'category' => UserCategory::EXTERNAL,
]);

$factory->state(User::class, 'faculty', [
    'category' => UserCategory::FACULTY_TEACHER,
]);

$factory->state(User::class, 'college', [
    'category' => UserCategory::COLLEGE_TEACHER,
]);
