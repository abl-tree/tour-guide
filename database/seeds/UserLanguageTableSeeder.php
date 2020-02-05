<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Models\Language;
use App\Models\UserLanguage;

class UserLanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::whereDoesntHave('languages')->get();

        $default_language = Language::where('alpha2', 'en')->first();

        if($default_language)
        foreach ($users as $key => $user) {
            $user->languages()->create([
                'language_id' => $default_language->id
            ]);
        }
    }
}
