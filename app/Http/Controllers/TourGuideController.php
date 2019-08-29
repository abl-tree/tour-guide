<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterTourGuide; 
use App\Mail\ApproveSubscription; 
use App\Models\UserAccessLevel;
use App\Models\AccessLevel;
use App\Models\UserInfo;
use App\User;
use Carbon\Carbon;
use Validator;


class TourGuideController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('profile.tour_guides');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {
        $tour_guides = User::whereHas('access_levels', function($q) {
            $q->where('access_level_id', 2);
        })->with('info')->get();

        return response()->json($tour_guides);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $user = User::find($id);
        
        if ($user->accepted_at) {
            $user->accepted_at = null;
            $user->save();
        } else {
            $user->accepted_at = Carbon::now();

            if($user->save())
            Mail::send(new ApproveSubscription($user));
        }

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id)->info();

        return $user->delete();
    }
    
    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('profile.register');
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users']
        ])->validate();

        event(new Registered($user = $this->createGuide($request->all())));

        $request->session()->flash('status', 'Tour guide was added successfully!');

        return back();
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    public function createGuide(array $data)
    {
        $info = UserInfo::create([
            'first_name' => $data['first_name'],
            'middle_ename' => $data['middle_name'],
            'last_name' => $data['last_name']
        ]);

        $data['password'] = Str::random(8);

        $user = User::create([
            'user_info_id' => $info->id,
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'accepted_at' => Carbon::now()
        ]);

        $access_level = AccessLevel::where('code', 'tg')->first();

        $access = UserAccessLevel::create([
            'user_id' => $user->id,
            'access_level_id' => $access_level->id
        ]); 

        $user['readable_password'] = $data['password'];

        Mail::send(new RegisterTourGuide($user));

        return $user;
    }
}
