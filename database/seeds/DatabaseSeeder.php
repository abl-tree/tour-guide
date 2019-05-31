<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\UserAccessLevel::class, 40)->create()
        ->each(function($user) {
            $user->user()->save(factory(App\Models\Schedule::class)->make());
        });
        // $this->call(AccessLevelTableSeeder::class);
        // $this->call(GenderTableSeeder::class);
        // $this->call(UserInfoTableSeeder::class);
        // $this->call(UsersTableSeeder::class);
        // $this->call(UserAccessLevelTableSeeder::class);
    }
}
