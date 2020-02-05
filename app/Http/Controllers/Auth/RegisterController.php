<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Models\UserInfo;
use App\Models\AccessLevel;
use App\Models\UserAccessLevel;
use App\Models\UserLanguage;
use App\Models\Language;
use App\Models\Gender;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewSubscriber; 

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        $gender = Gender::all();

        return view('auth.register', compact('gender'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $info = UserInfo::create([
            'first_name' => $data['first_name'],
            'middle_ename' => $data['middle_name'],
            'last_name' => $data['last_name']
        ]);

        $user = User::create([
            'user_info_id' => $info->id,
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $access_level = AccessLevel::where('code', 'tg')->first();

        $access = UserAccessLevel::create([
            'user_id' => $user->id,
            'access_level_id' => $access_level->id
        ]);

        $default_language = Language::where('alpha2', 'en')->first();

        if($default_language) {
            UserLanguage::create([
                'user_id' => $user->id,
                'language_id' => $default_language->id
            ]);
        }

        Mail::send(new NewSubscriber($user));

        return $user;
    }
}
